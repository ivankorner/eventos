<?php
/**
 * Script de diagnóstico para emails de inscripción
 * Ejecutar: php diagnose-emails.php
 */

// Cargar configuración
define('BASE_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . '/app/Views');

// Cargar .env
$envFile = file_exists(BASE_PATH . '/.env') ? BASE_PATH . '/.env' : BASE_PATH . '/DeployCorregido/.env';

echo "🔍 DIAGNÓSTICO DE EMAILS DE INSCRIPCIÓN\n";
echo "========================================\n\n";

if (!file_exists($envFile)) {
    echo "❌ No se encontró archivo .env\n";
    exit(1);
}

// Parsear .env
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

define('APP_NAME', $_ENV['APP_NAME'] ?? 'Sistema de Inscripciones');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/parlamentos/public');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_DEBUG', $_ENV['APP_DEBUG'] ?? true);

// Cargar clases necesarias
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/app/Helpers/Email.php';
require_once BASE_PATH . '/app/Helpers/ErrorHandler.php';
require_once BASE_PATH . '/app/Helpers/ConfigHelper.php';
require_once BASE_PATH . '/config/database.php';

try {
    // 1. Verificar configuración SMTP
    echo "1️⃣  CONFIGURACIÓN SMTP\n";
    echo "   Host: " . $_ENV['MAIL_HOST'] . "\n";
    echo "   Port: " . $_ENV['MAIL_PORT'] . "\n";
    echo "   Username: " . $_ENV['MAIL_USERNAME'] . "\n";
    echo "   From: " . $_ENV['MAIL_FROM_ADDRESS'] . "\n";
    echo "   ✓ Configuración cargada\n\n";

    // 2. Verificar conexión a BD
    echo "2️⃣  CONEXIÓN A BASE DE DATOS\n";
    $db = Database::getInstance();

    // Verificar tabla mail_queue
    $stmt = $db->query("SHOW TABLES LIKE 'mail_queue'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Tabla mail_queue existe\n";
    } else {
        echo "   ❌ Tabla mail_queue NO existe\n";
        exit(1);
    }

    // Contar emails en la cola
    $stmt = $db->query("SELECT COUNT(*) as total FROM mail_queue");
    $total = $stmt->fetch()['total'];
    echo "   Total emails en cola: $total\n";

    // Emails pendientes
    $stmt = $db->query("SELECT COUNT(*) as pending FROM mail_queue WHERE status = 'pending'");
    $pending = $stmt->fetch()['pending'];
    echo "   Emails PENDIENTES: $pending\n";

    // Emails enviados
    $stmt = $db->query("SELECT COUNT(*) as sent FROM mail_queue WHERE status = 'sent'");
    $sent = $stmt->fetch()['sent'];
    echo "   Emails ENVIADOS: $sent\n";

    // Emails fallidos
    $stmt = $db->query("SELECT COUNT(*) as failed FROM mail_queue WHERE status = 'failed'");
    $failed = $stmt->fetch()['failed'];
    echo "   Emails FALLIDOS: $failed\n\n";

    // 3. Mostrar últimos emails en la cola
    if ($total > 0) {
        echo "3️⃣  ÚLTIMOS 5 EMAILS EN LA COLA\n";
        $stmt = $db->query("SELECT id, to_email, subject, status, attempts, created_at FROM mail_queue ORDER BY id DESC LIMIT 5");
        $mails = $stmt->fetchAll();

        foreach ($mails as $mail) {
            $status_icon = match($mail['status']) {
                'pending' => '⏳',
                'sent' => '✅',
                'failed' => '❌'
            };
            echo "   $status_icon [$mail[id]] {$mail['to_email']}\n";
            echo "      Asunto: {$mail['subject']}\n";
            echo "      Estado: {$mail['status']} (intentos: {$mail['attempts']})\n";
            echo "      Creado: {$mail['created_at']}\n\n";
        }
    } else {
        echo "3️⃣  NO HAY EMAILS EN LA COLA\n\n";
    }

    // 4. Intentar procesar la cola
    echo "4️⃣  PROCESANDO COLA DE EMAILS...\n";
    require_once BASE_PATH . '/app/Helpers/Database.php';

    $result = Email::processQueue(5);
    echo "   Emails enviados: " . $result['sent'] . "\n";
    echo "   Emails fallidos: " . $result['failed'] . "\n";

    if ($result['sent'] > 0) {
        echo "   ✓ Se procesaron emails correctamente\n\n";
    }

    // 5. Estado final
    echo "5️⃣  ESTADO FINAL DE LA COLA\n";
    $stmt = $db->query("SELECT COUNT(*) as pending FROM mail_queue WHERE status = 'pending'");
    $pending = $stmt->fetch()['pending'];
    echo "   Emails aún pendientes: $pending\n";

    if ($pending == 0) {
        echo "   ✓ Cola vacía - Todos los emails han sido procesados\n";
    } else {
        echo "   ⚠️  Hay $pending emails aún pendientes\n";
    }

    echo "\n✅ Diagnóstico completado\n";

} catch (\Throwable $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
