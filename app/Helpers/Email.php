<?php
/**
 * Wrapper de PHPMailer para envío de emails
 * También gestiona la cola de emails (mail_queue)
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

class Email
{
    /**
     * Envía un email directamente (síncrono)
     *
     * @param string $toEmail   Dirección del destinatario
     * @param string $toName    Nombre del destinatario
     * @param string $subject   Asunto
     * @param string $bodyHtml  Cuerpo HTML
     * @throws RuntimeException Si falla el envío
     */
    public static function send(string $toEmail, string $toName, string $subject, string $bodyHtml): void
    {
        $config = require BASE_PATH . '/config/mail.php';

        $mail = new PHPMailer(true);

        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();
            $mail->Host        = $config['host'];
            $mail->SMTPAuth    = true;
            $mail->Username    = $config['username'];
            $mail->Password    = $config['password'];
            $mail->SMTPSecure  = $config['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port        = $config['port'];
            $mail->CharSet     = 'UTF-8';
            $mail->Timeout     = 30;
            $mail->SMTPKeepAlive = false;

            // Log de configuración para diagnóstico (sin exponer password completo)
            $maskedPass = substr($config['password'], 0, 3) . '***' . substr($config['password'], -2);
            ErrorHandler::log("SMTP Config: host={$config['host']}, port={$config['port']}, user={$config['username']}, pass={$maskedPass}, encryption={$config['encryption']}");

            // Remitente
            $mail->setFrom($config['from_address'], $config['from_name']);

            // Destinatario
            $mail->addAddress($toEmail, $toName);

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = strip_tags($bodyHtml);

            $mail->send();
            ErrorHandler::log("Email enviado exitosamente a {$toEmail} via {$config['host']}");
        } catch (MailerException $e) {
            ErrorHandler::log("Error enviando email a {$toEmail} con servidor {$config['host']}: " . $mail->ErrorInfo);

            // FALLBACK: Intentar con Gmail si falla el servidor primario
            if (strpos($config['host'], 'appcde.online') !== false) {
                ErrorHandler::log("Intentando fallback con Gmail para {$toEmail}");
                try {
                    self::sendViaGmailFallback($toEmail, $toName, $subject, $bodyHtml);
                    return; // Gmail funcionó, salimos
                } catch (\Throwable $gmailEx) {
                    ErrorHandler::log("Gmail fallback también falló: " . $gmailEx->getMessage());
                }
            }
            throw new \RuntimeException('No se pudo enviar el email: ' . $mail->ErrorInfo);
        }
    }

    /**
     * Envío fallback mediante Gmail (requiere APP_PASSWORD configurada en .env)
     */
    private static function sendViaGmailFallback(string $toEmail, string $toName, string $subject, string $bodyHtml): void
    {
        $gmailUser = $_ENV['GMAIL_FALLBACK_USER'] ?? null;
        $gmailPass = $_ENV['GMAIL_FALLBACK_PASSWORD'] ?? null;

        if (!$gmailUser || !$gmailPass) {
            throw new \RuntimeException('No se pudo enviar el email: servidor SMTP principal falló y Gmail fallback no está configurado');
        }

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $gmailUser;
            $mail->Password   = $gmailPass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($gmailUser, $_ENV['APP_NAME'] ?? 'Sistema de Inscripciones');
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $bodyHtml;
            $mail->AltBody = strip_tags($bodyHtml);

            $mail->send();
            ErrorHandler::log("Email enviado a {$toEmail} via Gmail fallback");
        } catch (MailerException $e) {
            ErrorHandler::log("Gmail fallback también falló para {$toEmail}: " . $mail->ErrorInfo);
            throw new \RuntimeException('No se pudo enviar el email via Gmail: ' . $mail->ErrorInfo);
        }
    }

    /**
     * Encola un email para envío asíncrono
     * Más seguro en shared hosting donde SMTP puede tardar
     */
    public static function queue(string $toEmail, string $toName, string $subject, string $bodyHtml): void
    {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO mail_queue (to_email, to_name, subject, body_html) VALUES (:to, :name, :subject, :body)"
        )->execute([
            ':to'      => $toEmail,
            ':name'    => $toName,
            ':subject' => $subject,
            ':body'    => $bodyHtml,
        ]);
    }

    /**
     * Procesa emails pendientes en la cola (ejecutar periódicamente)
     * Llamar desde un script CRON o al final de cada request
     */
    public static function processQueue(int $limit = 10): array
    {
        $db   = Database::getInstance();
        $sent = 0;
        $fail = 0;

        try {
            $stmt = $db->prepare(
                "SELECT * FROM mail_queue WHERE status = 'pending' AND attempts < 3 ORDER BY created_at ASC LIMIT :limit"
            );
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $mails = $stmt->fetchAll();

            if (empty($mails)) {
                return ['sent' => 0, 'failed' => 0];
            }

            foreach ($mails as $mail) {
                try {
                    self::send($mail['to_email'], $mail['to_name'] ?? '', $mail['subject'], $mail['body_html']);

                    $db->prepare(
                        "UPDATE mail_queue SET status = 'sent', sent_at = NOW() WHERE id = :id"
                    )->execute([':id' => $mail['id']]);

                    $sent++;
                } catch (\RuntimeException $e) {
                    $db->prepare(
                        "UPDATE mail_queue SET attempts = attempts + 1, status = IF(attempts >= 2, 'failed', 'pending') WHERE id = :id"
                    )->execute([':id' => $mail['id']]);

                    $fail++;
                    ErrorHandler::log("Error enviando email a {$mail['to_email']}: " . $e->getMessage());
                } catch (\Throwable $e) {
                    $db->prepare(
                        "UPDATE mail_queue SET attempts = attempts + 1 WHERE id = :id"
                    )->execute([':id' => $mail['id']]);

                    $fail++;
                    ErrorHandler::log("Error inesperado procesando email {$mail['id']}: " . $e->getMessage());
                }
            }
        } catch (\Throwable $e) {
            ErrorHandler::log("Error crítico procesando cola: " . $e->getMessage());
        }

        return ['sent' => $sent, 'failed' => $fail];
    }

    /**
     * Construye el HTML de email de bienvenida para nuevo usuario
     */
    public static function buildWelcomeHtml(array $user, string $tempPassword): string
    {
        $appName = htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8');
        $name    = htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8');
        $email   = htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8');
        $pass    = htmlspecialchars($tempPassword, ENT_QUOTES, 'UTF-8');
        $url     = APP_URL . '/admin/login';

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"><title>Bienvenido a {$appName}</title></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px">
          <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden">
            <div style="background:#4f46e5;padding:30px;text-align:center">
              <h1 style="color:#fff;margin:0;font-size:24px">{$appName}</h1>
            </div>
            <div style="padding:30px">
              <h2>¡Bienvenido/a, {$name}!</h2>
              <p>Se creó tu cuenta de acceso al sistema. Tus credenciales son:</p>
              <table style="width:100%;border-collapse:collapse;margin:20px 0">
                <tr><td style="padding:8px;background:#f9f9f9;font-weight:bold">Email:</td><td style="padding:8px">{$email}</td></tr>
                <tr><td style="padding:8px;background:#f9f9f9;font-weight:bold">Contraseña temporal:</td><td style="padding:8px;font-family:monospace;font-size:16px">{$pass}</td></tr>
              </table>
              <p style="color:#e53e3e"><strong>Importante:</strong> Deberás cambiar tu contraseña al primer ingreso.</p>
              <div style="text-align:center;margin:30px 0">
                <a href="{$url}" style="background:#4f46e5;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:16px">Ingresar al sistema</a>
              </div>
              <p style="color:#666;font-size:14px">Si no solicitaste esta cuenta, podés ignorar este email.</p>
            </div>
          </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Construye el HTML de confirmación de inscripción para el inscripto (voucher)
     */
    public static function buildConfirmationHtml(array $event, array $formFields, array $responseData, int $subId = 0): string
    {
        $appName    = htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8');
        $eventTitle = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
        $eventDesc  = htmlspecialchars($event['description'] ?? '', ENT_QUOTES, 'UTF-8');
        $location   = htmlspecialchars($event['location'] ?? '', ENT_QUOTES, 'UTF-8');
        $refNumber  = $subId > 0 ? 'INS-' . str_pad($subId, 6, '0', STR_PAD_LEFT) : '';
        $issuedAt   = date('d/m/Y H:i');

        // Fecha(s) del evento
        $dateHtml = '';
        if (!empty($event['start_date'])) {
            $start = date('d/m/Y', strtotime($event['start_date']));
            $dateHtml = $start;
            if (!empty($event['end_date']) && $event['end_date'] !== $event['start_date']) {
                $dateHtml .= ' al ' . date('d/m/Y', strtotime($event['end_date']));
            }
        }

        // Imagen de portada
        $coverImageHtml = '';
        if (!empty($event['cover_image'])) {
            $coverImageUrl = htmlspecialchars(APP_URL . '/' . $event['cover_image'], ENT_QUOTES, 'UTF-8');
            $coverImageHtml = "<img src='{$coverImageUrl}' alt='{$eventTitle}' style='width:100%;height:200px;object-fit:cover;display:block;'>";
        }

        // Fila de datos del evento (fecha, lugar)
        $eventMeta = '';
        if ($dateHtml) {
            $eventMeta .= "<td style='padding:10px 16px;vertical-align:top;width:50%'><span style='display:block;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-bottom:4px'>Fecha</span><span style='font-size:15px;font-weight:600;color:#1f2937'>{$dateHtml}</span></td>";
        }
        if ($location) {
            $eventMeta .= "<td style='padding:10px 16px;vertical-align:top;width:50%'><span style='display:block;font-size:11px;text-transform:uppercase;letter-spacing:1px;color:#9ca3af;margin-bottom:4px'>Lugar</span><span style='font-size:15px;font-weight:600;color:#1f2937'>{$location}</span></td>";
        }
        $eventMetaRow = $eventMeta ? "<table width='100%' style='border-collapse:collapse;border-top:1px dashed #e5e7eb;margin-top:16px'><tr>{$eventMeta}</tr></table>" : '';

        // Filas de datos del inscripto
        $rows = '';
        $bg = false;
        foreach ($formFields as $field) {
            if (in_array($field['type'], ['heading', 'paragraph'], true)) {
                continue;
            }
            $label = htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $value = $responseData[$field['id']] ?? '—';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $value   = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            $rowBg   = $bg ? '#f9fafb' : '#ffffff';
            $rows   .= "<tr style='background:{$rowBg}'>
                          <td style='padding:10px 16px;font-size:13px;color:#6b7280;width:40%;border-bottom:1px solid #f3f4f6'>{$label}</td>
                          <td style='padding:10px 16px;font-size:14px;font-weight:600;color:#111827;border-bottom:1px solid #f3f4f6'>{$value}</td>
                        </tr>";
            $bg = !$bg;
        }

        // Número de referencia (solo si existe)
        $refHtml = $refNumber
            ? "<div style='text-align:center;margin-bottom:24px'>
                 <span style='display:inline-block;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;font-family:monospace;font-size:18px;font-weight:700;letter-spacing:2px;padding:8px 20px;border-radius:6px'>{$refNumber}</span>
                 <div style='font-size:11px;color:#9ca3af;margin-top:6px;text-transform:uppercase;letter-spacing:1px'>N&uacute;mero de inscripci&oacute;n</div>
               </div>"
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Comprobante de inscripci&oacute;n</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:32px 16px">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%">

  <!-- CABECERA -->
  <tr>
    <td style="background:#4338ca;padding:24px 32px;border-radius:12px 12px 0 0;text-align:center">
      <p style="margin:0;color:#c7d2fe;font-size:12px;text-transform:uppercase;letter-spacing:2px">{$appName}</p>
      <h1 style="margin:8px 0 0;color:#ffffff;font-size:22px;font-weight:700">Comprobante de Inscripci&oacute;n</h1>
    </td>
  </tr>

  <!-- IMAGEN PORTADA -->
  {$coverImageHtml}

  <!-- CUERPO PRINCIPAL -->
  <tr>
    <td style="background:#ffffff;padding:32px">

      <!-- BADGE CONFIRMADO -->
      <div style="text-align:center;margin-bottom:28px">
        <span style="display:inline-block;background:#dcfce7;border:2px solid #86efac;color:#166534;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:2px;padding:6px 18px;border-radius:99px">
          &#10003; Inscripci&oacute;n Confirmada
        </span>
      </div>

      <!-- NÚMERO DE REFERENCIA -->
      {$refHtml}

      <!-- DATOS DEL EVENTO -->
      <div style="background:#fafafa;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:28px">
        <div style="background:#4338ca;padding:10px 16px">
          <span style="color:#e0e7ff;font-size:12px;text-transform:uppercase;letter-spacing:1px;font-weight:600">Evento</span>
        </div>
        <div style="padding:16px">
          <h2 style="margin:0 0 6px;font-size:18px;color:#111827;font-weight:700">{$eventTitle}</h2>
          {$eventMetaRow}
        </div>
      </div>

      <!-- DATOS DEL INSCRIPTO -->
      <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:28px">
        <div style="background:#f9fafb;padding:10px 16px;border-bottom:1px solid #e5e7eb">
          <span style="color:#374151;font-size:12px;text-transform:uppercase;letter-spacing:1px;font-weight:600">Datos del inscripto</span>
        </div>
        <table width="100%" cellpadding="0" cellspacing="0">{$rows}</table>
      </div>

      <!-- PIE -->
      <p style="margin:0;font-size:13px;color:#6b7280;text-align:center">
        Comprobante emitido el {$issuedAt}.<br>
        Conserv&aacute; este email como constancia de tu inscripci&oacute;n.
      </p>

    </td>
  </tr>

  <!-- FOOTER -->
  <tr>
    <td style="background:#1e1b4b;padding:16px 32px;border-radius:0 0 12px 12px;text-align:center">
      <p style="margin:0;color:#a5b4fc;font-size:12px">{$appName} &mdash; No contestar este mail.</p>
    </td>
  </tr>

</table>
</td></tr>
</table>
</body>
</html>
HTML;
    }

    /**
     * Construye el HTML de notificación de nueva inscripción al organizador
     */
    public static function buildNotificationHtml(array $event, array $formFields, array $responseData, string $submittedAt, string $ip): string
    {
        $appName    = htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8');
        $eventTitle = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
        $rows = '';

        foreach ($formFields as $field) {
            if (in_array($field['type'], ['heading', 'paragraph'], true)) {
                continue;
            }
            $label = htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8');
            $value = $responseData[$field['id']] ?? '—';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $value = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            $rows .= "<tr><td style='padding:8px;background:#f9f9f9;font-weight:bold'>{$label}</td><td style='padding:8px'>{$value}</td></tr>";
        }

        $at  = htmlspecialchars($submittedAt, ENT_QUOTES, 'UTF-8');
        $ipH = htmlspecialchars($ip, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es"><head><meta charset="UTF-8"><title>Nueva inscripción</title></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px">
          <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden">
            <div style="background:#059669;padding:30px;text-align:center">
              <h1 style="color:#fff;margin:0;font-size:24px">Nueva inscripción recibida</h1>
            </div>
            <div style="padding:30px">
              <p>Hay una nueva inscripción en <strong>{$eventTitle}</strong> recibida el <strong>{$at}</strong> desde la IP <code>{$ipH}</code>.</p>
              <table style="width:100%;border-collapse:collapse;margin:20px 0">{$rows}</table>
              <p style="color:#666;font-size:12px">— {$appName}</p>
            </div>
          </div>
        </body></html>
        HTML;
    }
}
