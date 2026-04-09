<?php
/**
 * Script de diagnóstico — Prueba conexión a BD
 *
 * Uso: Sube este archivo a /eventos/ en Don Web
 * Luego accede: https://appcde.online/eventos/test-db-connection.php
 */

// Leer .env
$envPath = __DIR__ . '/.env';
if (!file_exists($envPath)) {
    die("❌ Archivo .env no encontrado en " . __DIR__);
}

$env = parse_ini_file($envPath);

if (!$env) {
    die("❌ No se pudo leer el archivo .env");
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Conexión BD</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        pre { background: #f4f4f4; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Prueba de Conexión a BD</h1>

    <h2>📋 Datos leídos desde .env:</h2>
    <pre>
Host: <?php echo $env['DB_HOST'] ?? 'NO CONFIGURADO'; ?>
Puerto: <?php echo $env['DB_PORT'] ?? '3306'; ?>
BD: <?php echo $env['DB_NAME'] ?? 'NO CONFIGURADO'; ?>
Usuario: <?php echo $env['DB_USER'] ?? 'NO CONFIGURADO'; ?>
Contraseña: <?php echo '***' . substr($env['DB_PASS'] ?? '', -5); // Mostrar últimos 5 caracteres ?>
    </pre>

    <h2>🔗 Intentando conectar...</h2>
    <?php

    $host = $env['DB_HOST'] ?? 'localhost';
    $port = $env['DB_PORT'] ?? '3306';
    $dbname = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        echo '<p class="success">✅ CONEXIÓN EXITOSA</p>';

        // Verificar tablas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        echo '<p><strong>Tablas en la BD:</strong></p>';
        echo '<ul>';
        foreach ($tables as $table) {
            echo '<li>' . htmlspecialchars($table) . '</li>';
        }
        echo '</ul>';

        if (count($tables) > 0) {
            echo '<p class="success">✅ BD importada correctamente</p>';
        } else {
            echo '<p class="warning">⚠️ BD vacía - necesita importar inscripciones_db_*.sql</p>';
        }

    } catch (PDOException $e) {
        echo '<p class="error">❌ ERROR DE CONEXIÓN</p>';
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';

        echo '<h3>Posibles problemas:</h3>';
        echo '<ul>';
        echo '<li>❌ Contraseña incorrecta</li>';
        echo '<li>❌ Host/Puerto incorrectos</li>';
        echo '<li>❌ BD no existe</li>';
        echo '<li>❌ Usuario sin permisos</li>';
        echo '</ul>';

        echo '<h3>Qué hacer:</h3>';
        echo '<ol>';
        echo '<li>Verifica credenciales en el archivo .env</li>';
        echo '<li>Verifica en phpMyAdmin que BD existe</li>';
        echo '<li>Verifica que el usuario tiene permisos</li>';
        echo '<li>Contacta a Don Web si persiste</li>';
        echo '</ol>';
    }

    ?>

    <hr>

    <h2>ℹ️ Información del Servidor:</h2>
    <pre>
PHP Version: <?php echo phpversion(); ?>
MySQL Driver: <?php echo extension_loaded('pdo_mysql') ? 'PDO MySQL ✓' : 'PDO MySQL ✗'; ?>
    </pre>

    <hr>

    <p style="font-size: 12px; color: gray;">
        <strong>⚠️ Seguridad:</strong> Elimina este archivo después de probar.
    </p>

</body>
</html>
