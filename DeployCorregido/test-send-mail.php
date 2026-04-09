<?php
/**
 * Script para enviar un correo de prueba
 * Uso: php test-send-mail.php tu@email.com
 *
 * Envía un correo real para verificar que todo funciona
 */

// Cargar configuración
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/app/Views');

// Cargar .env
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    echo "❌ No se encontró .env\n";
    exit(1);
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);

    if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
        (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
        $value = substr($value, 1, -1);
    }

    $_ENV[$key] = $value;
    putenv("$key=$value");
}

// Definir constantes
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Sistema de Inscripciones');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/parlamentos/public');

// Cargar vendor y clases
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/Helpers/Email.php';
require_once BASE_PATH . '/app/Helpers/ErrorHandler.php';
require_once BASE_PATH . '/app/Helpers/ConfigHelper.php';
require_once BASE_PATH . '/config/database.php';

echo "═══════════════════════════════════════════════════════════\n";
echo "📧 PRUEBA DE ENVÍO DE CORREO\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Validar argumento
if ($argc < 2) {
    echo "❌ Uso: php test-send-mail.php <email_destino>\n\n";
    echo "Ejemplo:\n";
    echo "  php test-send-mail.php usuario@example.com\n";
    exit(1);
}

$toEmail = trim($argv[1]);

// Validar email
if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Email inválido: $toEmail\n";
    exit(1);
}

echo "📨 Enviando correo de prueba a: $toEmail\n\n";

try {
    // Construir HTML simple
    $subject = "Prueba de SMTP - " . date('Y-m-d H:i:s');
    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head><meta charset="UTF-8"></head>
    <body style="font-family:Arial;background:#f4f4f4;padding:20px">
      <div style="max-width:600px;margin:0 auto;background:#fff;padding:30px;border-radius:8px">
        <h2>✅ Prueba de Configuración SMTP</h2>
        <p>Este correo fue enviado exitosamente desde el servidor appcde.online</p>

        <table style="margin:20px 0;width:100%;border-collapse:collapse">
          <tr>
            <td style="padding:8px;background:#f9f9f9;font-weight:bold">Host SMTP:</td>
            <td style="padding:8px">{$_ENV['MAIL_HOST']}</td>
          </tr>
          <tr>
            <td style="padding:8px;background:#f9f9f9;font-weight:bold">Puerto:</td>
            <td style="padding:8px">{$_ENV['MAIL_PORT']}</td>
          </tr>
          <tr>
            <td style="padding:8px;background:#f9f9f9;font-weight:bold">Encryption:</td>
            <td style="padding:8px">{$_ENV['MAIL_ENCRYPTION']}</td>
          </tr>
          <tr>
            <td style="padding:8px;background:#f9f9f9;font-weight:bold">Remitente:</td>
            <td style="padding:8px">{$_ENV['MAIL_FROM_ADDRESS']}</td>
          </tr>
          <tr>
            <td style="padding:8px;background:#f9f9f9;font-weight:bold">Hora:</td>
            <td style="padding:8px">
HTML;
    $html .= date('Y-m-d H:i:s', time());
    $html .= <<<HTML
            </td>
          </tr>
        </table>

        <p style="color:#666;font-size:12px">Sistema de Inscripciones</p>
      </div>
    </body>
    </html>
HTML;

    // Enviar email
    Email::send($toEmail, 'Usuario de Prueba', $subject, $html);

    echo "✅ Correo enviado exitosamente!\n";
    echo "\n📬 Verifica tu bandeja de entrada en: $toEmail\n";
    echo "\n   Si no lo ves en 5 minutos:\n";
    echo "   • Revisa la carpeta de Spam\n";
    echo "   • Verifica que el email sea accesible\n";

} catch (\Throwable $e) {
    echo "❌ Error al enviar el correo:\n";
    echo "   " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n═══════════════════════════════════════════════════════════\n";
?>
