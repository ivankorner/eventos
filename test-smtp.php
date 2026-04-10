<?php
/**
 * Script de prueba SMTP directo
 * Ejecutar: php test-smtp.php
 * O acceder via web: /parlamentos/public/test-smtp.php (luego BORRAR)
 *
 * Prueba diferentes configuraciones SMTP para encontrar la que funciona
 */

define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as MailerException;

// Cargar .env
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

$host     = $_ENV['MAIL_HOST']         ?? 'appcde.online';
$username = $_ENV['MAIL_USERNAME']     ?? 'no-reply@appcde.online';
$password = $_ENV['MAIL_PASSWORD']     ?? '';
$from     = $_ENV['MAIL_FROM_ADDRESS'] ?? $username;

$isCli = (php_sapi_name() === 'cli');
$nl = $isCli ? "\n" : "<br>";

echo $isCli ? "" : "<pre>";
echo "=== PRUEBA DE CONEXION SMTP ===$nl$nl";
echo "Host: $host$nl";
echo "Username: $username$nl";
echo "Password: " . substr($password, 0, 3) . '***' . substr($password, -2) . " (longitud: " . strlen($password) . ")$nl$nl";

// Configuraciones a probar
$configs = [
    ['desc' => 'TLS en puerto 587', 'port' => 587, 'enc' => PHPMailer::ENCRYPTION_STARTTLS],
    ['desc' => 'SSL en puerto 465', 'port' => 465, 'enc' => PHPMailer::ENCRYPTION_SMTPS],
    ['desc' => 'Sin encriptacion puerto 25', 'port' => 25, 'enc' => ''],
    ['desc' => 'TLS en puerto 465', 'port' => 465, 'enc' => PHPMailer::ENCRYPTION_STARTTLS],
    ['desc' => 'SSL en puerto 587', 'port' => 587, 'enc' => PHPMailer::ENCRYPTION_SMTPS],
];

$testEmail = $username; // Enviar a sí mismo para prueba

foreach ($configs as $i => $cfg) {
    echo "--- Prueba " . ($i + 1) . ": {$cfg['desc']} ---$nl";

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER;
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->Port       = $cfg['port'];
        $mail->Timeout    = 15;
        $mail->CharSet    = 'UTF-8';

        if ($cfg['enc']) {
            $mail->SMTPSecure = $cfg['enc'];
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        // Capturar debug output
        ob_start();

        $mail->setFrom($from, 'Test SMTP');
        $mail->addAddress($testEmail, 'Test');
        $mail->isHTML(true);
        $mail->Subject = 'Prueba SMTP - ' . date('Y-m-d H:i:s');
        $mail->Body    = '<h2>Prueba de email</h2><p>Si ves esto, la configuracion SMTP funciona con: <strong>' . $cfg['desc'] . '</strong></p>';

        $mail->send();
        $debug = ob_get_clean();

        echo "EXITO! Email enviado con: {$cfg['desc']}$nl";
        echo ">>> USA ESTA CONFIGURACION EN .env:$nl";
        echo "    MAIL_PORT={$cfg['port']}$nl";
        echo "    MAIL_ENCRYPTION=" . ($cfg['enc'] === PHPMailer::ENCRYPTION_SMTPS ? 'ssl' : ($cfg['enc'] === PHPMailer::ENCRYPTION_STARTTLS ? 'tls' : 'none')) . "$nl$nl";
        break; // Encontramos una que funciona

    } catch (MailerException $e) {
        ob_end_clean();
        echo "FALLO: " . $mail->ErrorInfo . "$nl$nl";
    }
}

echo "=== FIN DE PRUEBAS ===$nl";
echo $isCli ? "" : "</pre>";
