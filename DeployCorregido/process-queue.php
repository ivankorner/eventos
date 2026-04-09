<?php
/**
 * Script para procesar la cola de correos
 * Ejecutar: php process-queue.php
 */

// Cargar configuración
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/app/Views');

// Cargar .env - intentar primero DeployCorregido, luego raíz
$envFile = BASE_PATH . '/DeployCorregido/.env';
if (!file_exists($envFile)) {
    $envFile = BASE_PATH . '/.env';
}

echo "Cargando .env desde: $envFile\n";

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Saltar comentarios
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;

        // Parsear KEY=VALUE
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remover comillas
        if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
            (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
    echo "✓ Variables de entorno cargadas\n\n";
} else {
    echo "❌ No se encontró .env\n";
    exit(1);
}

// Definir constantes de app
define('APP_NAME', $_ENV['APP_NAME'] ?? 'Sistema de Inscripciones');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/parlamentos/public');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// Cargar autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Cargar clases
require_once BASE_PATH . '/app/Helpers/Email.php';
require_once BASE_PATH . '/app/Helpers/ErrorHandler.php';
require_once BASE_PATH . '/app/Helpers/ConfigHelper.php';
require_once BASE_PATH . '/config/database.php';

try {
    // Conectar a la base de datos
    $db = Database::getInstance();

    echo "Procesando cola de correos...\n";
    $result = Email::processQueue(50); // Procesar máximo 50 correos

    echo "✓ Correos enviados: " . $result['sent'] . "\n";
    echo "✗ Correos fallidos: " . $result['failed'] . "\n";

    // Mostrar estado de la cola
    $stmt = $db->prepare("SELECT COUNT(*) as total,
                                 SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                                 SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
                                 SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed
                          FROM mail_queue");
    $stmt->execute();
    $status = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "\n📧 Estado de la cola:\n";
    echo "  Total: " . $status['total'] . "\n";
    echo "  Pendientes: " . $status['pending'] . "\n";
    echo "  Enviados: " . $status['sent'] . "\n";
    echo "  Fallidos: " . $status['failed'] . "\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
