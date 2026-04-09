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
        } catch (MailerException $e) {
            ErrorHandler::log("Error enviando email a {$toEmail}: " . $mail->ErrorInfo);
            throw new \RuntimeException('No se pudo enviar el email: ' . $mail->ErrorInfo);
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

        $stmt = $db->prepare(
            "SELECT * FROM mail_queue WHERE status = 'pending' AND attempts < 3 ORDER BY created_at ASC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $mails = $stmt->fetchAll();

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
            }
        }

        return ['sent' => $sent, 'failed' => $fail];
    }

    /**
     * Construye el HTML de email de bienvenida para nuevo usuario
     */
    public static function buildWelcomeHtml(array $user, string $tempPassword): string
    {
        $appName = htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8');
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
     * Construye el HTML de confirmación de inscripción para el inscripto
     */
    public static function buildConfirmationHtml(array $event, array $formFields, array $responseData): string
    {
        $appName   = htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8');
        $eventTitle = htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8');
        $rows = '';

        // Cruzar campo a campo con su label
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

        return <<<HTML
        <!DOCTYPE html>
        <html lang="es">
        <head><meta charset="UTF-8"><title>Confirmación de inscripción</title></head>
        <body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px">
          <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden">
            <div style="background:#4f46e5;padding:30px;text-align:center">
              <h1 style="color:#fff;margin:0;font-size:24px">{$appName}</h1>
            </div>
            <div style="padding:30px">
              <h2>¡Tu inscripción fue recibida!</h2>
              <p>Gracias por inscribirte a <strong>{$eventTitle}</strong>. A continuación el resumen de tus datos:</p>
              <table style="width:100%;border-collapse:collapse;margin:20px 0">{$rows}</table>
              <p style="color:#666;font-size:14px">Si tenés alguna consulta, respondé este email o contactá a los organizadores del evento.</p>
            </div>
          </div>
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
