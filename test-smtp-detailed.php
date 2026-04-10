<?php
/**
 * Test detallado de conexión SMTP
 */

// Cargar .env
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    die("❌ No se encontró .env\n");
}

$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);

    // Remover comillas
    if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
        (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
        $value = substr($value, 1, -1);
    }

    $_ENV[$key] = $value;
}

echo "🔍 TEST SMTP DETALLADO\n";
echo "=====================\n\n";
echo "Configuración:\n";
echo "  Host: " . $_ENV['MAIL_HOST'] . "\n";
echo "  Port: " . $_ENV['MAIL_PORT'] . "\n";
echo "  Username: " . $_ENV['MAIL_USERNAME'] . "\n";
echo "  Encryption: " . $_ENV['MAIL_ENCRYPTION'] . "\n\n";

// Cargar PHPMailer
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailerException;

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; // Verbose debug output

try {
    echo "Configurando SMTP...\n";
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USERNAME'];
    $mail->Password   = $_ENV['MAIL_PASSWORD'];
    $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = $_ENV['MAIL_PORT'];
    $mail->CharSet    = 'UTF-8';

    echo "Intentando conectar y autenticar...\n\n";

    // Intentar conectar
    if (!$mail->smtpConnect()) {
        echo "❌ Error de conexión SMTP:\n";
        echo $mail->ErrorInfo . "\n";
        exit(1);
    }

    echo "✅ Conexión SMTP exitosa!\n";
    $mail->smtpClose();

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nErrorInfo: " . $mail->ErrorInfo . "\n";
    exit(1);
}
