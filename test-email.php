<?php
/**
 * Script de prueba para verificar el envío de correos
 * Ejecutar: php test-email.php
 */

// Cargar configuración
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/app/Views');

// Cargar .env
$envFile = BASE_PATH . '/DeployCorregido/.env';
if (!file_exists($envFile)) {
    $envFile = BASE_PATH . '/.env';
}

echo "📧 Prueba de envío de correo\n";
echo "============================\n\n";

if (file_exists($envFile)) {
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
} else {
    echo "❌ No se encontró .env\n";
    exit(1);
}

define('APP_NAME', $_ENV['APP_NAME'] ?? 'Sistema de Inscripciones');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/parlamentos/public');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// Cargar autoloader
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/Helpers/Email.php';
require_once BASE_PATH . '/app/Helpers/ErrorHandler.php';
require_once BASE_PATH . '/app/Helpers/ConfigHelper.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/config/mail.php';

$config = require BASE_PATH . '/config/mail.php';

echo "Configuración SMTP:\n";
echo "  Host: " . $config['host'] . "\n";
echo "  Port: " . $config['port'] . "\n";
echo "  Username: " . $config['username'] . "\n";
echo "  Encryption: " . $config['encryption'] . "\n";
echo "  From: " . $config['from_address'] . " (" . $config['from_name'] . ")\n\n";

try {
    // Conectar a la base de datos
    $db = Database::getInstance();
    echo "✓ Conexión a BD exitosa\n\n";

    // Enviar un email de prueba
    echo "Enviando email de prueba...\n";
    Email::send(
        'test@ejemplo.com',
        'Usuario Prueba',
        'Prueba de envío desde ' . APP_NAME,
        Email::buildWelcomeHtml(
            ['name' => 'Usuario Prueba', 'email' => 'test@ejemplo.com'],
            'TestPass123'
        )
    );

    echo "✓ Email enviado exitosamente\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
