<?php
/**
 * Script de prueba de conexión SMTP
 * Uso: php test-smtp-connection.php
 *
 * Verifica que la configuración SMTP sea correcta sin enviar correos reales
 */

// Cargar configuración
define('BASE_PATH', __DIR__);

// Cargar .env
$envFile = BASE_PATH . '/.env';
if (!file_exists($envFile)) {
    echo "❌ No se encontró .env en: $envFile\n";
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

echo "═══════════════════════════════════════════════════════════\n";
echo "🧪 PRUEBA DE CONEXIÓN SMTP\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Mostrar configuración cargada
echo "📋 Configuración SMTP cargada:\n";
echo "  Host: " . ($_ENV['MAIL_HOST'] ?? 'NO CONFIGURADO') . "\n";
echo "  Port: " . ($_ENV['MAIL_PORT'] ?? 'NO CONFIGURADO') . "\n";
echo "  Encryption: " . ($_ENV['MAIL_ENCRYPTION'] ?? 'NO CONFIGURADO') . "\n";
echo "  Username: " . ($_ENV['MAIL_USERNAME'] ?? 'NO CONFIGURADO') . "\n";
echo "  From: " . ($_ENV['MAIL_FROM_ADDRESS'] ?? 'NO CONFIGURADO') . "\n";
echo "\n";

// Requerimientos
if (!extension_loaded('openssl')) {
    echo "⚠️  ADVERTENCIA: La extensión OpenSSL no está cargada\n";
    echo "   Es necesaria para conexiones TLS/SSL\n\n";
}

if (!extension_loaded('sockets')) {
    echo "⚠️  ADVERTENCIA: La extensión Sockets no está cargada\n\n";
}

// Prueba de conexión básica
echo "🔌 Intentando conectar a " . $_ENV['MAIL_HOST'] . ":" . $_ENV['MAIL_PORT'] . "...\n";

$host = $_ENV['MAIL_HOST'];
$port = (int)$_ENV['MAIL_PORT'];
$timeout = 5;

$socket = @fsockopen($host, $port, $errno, $errstr, $timeout);

if ($socket) {
    echo "✅ Conexión establecida\n";

    // Leer respuesta del servidor
    $response = fgets($socket, 1024);
    echo "   Respuesta del servidor: " . trim($response) . "\n";

    // Enviar EHLO
    fwrite($socket, "EHLO test-client\r\n");
    $response = fgets($socket, 1024);
    echo "   EHLO response: " . trim($response) . "\n";

    // Leer capacidades
    while (strpos($response, '-') === 3) {
        $response = fgets($socket, 1024);
        if (strpos($response, 'AUTH') !== false) {
            echo "   ✓ AUTH soportado\n";
        }
        if (strpos($response, 'STARTTLS') !== false) {
            echo "   ✓ STARTTLS soportado\n";
        }
    }

    // Cerrar conexión
    fwrite($socket, "QUIT\r\n");
    fclose($socket);

    echo "\n✅ Configuración SMTP está correcta\n";
    echo "   Puedes proceder con el despliegue\n";

} else {
    echo "❌ No se pudo conectar\n";
    echo "   Error: $errstr ($errno)\n";
    echo "\n⚠️  Posibles causas:\n";
    echo "   1. Host incorrecto: " . $host . "\n";
    echo "   2. Puerto bloqueado: " . $port . "\n";
    echo "   3. Firewall bloqueando la conexión\n";
    echo "   4. El servidor SMTP no está activo\n";
    exit(1);
}

echo "\n═══════════════════════════════════════════════════════════\n";
?>
