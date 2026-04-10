<?php
/**
 * Configuración de correo electrónico
 * Inteligente: usa diferentes servidores según el entorno
 */

// En desarrollo local, usar Gmail o fallback; en producción, usar appcde.online
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', 'localhost:8080', 'localhost:3000'], true) ||
           (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'local');

$config = [
    'driver'       => $_ENV['MAIL_DRIVER']       ?? 'smtp',
    'host'         => $_ENV['MAIL_HOST']          ?? 'smtp.gmail.com',
    'port'         => (int)($_ENV['MAIL_PORT']    ?? 587),
    'encryption'   => $_ENV['MAIL_ENCRYPTION']    ?? 'tls',
    'username'     => $_ENV['MAIL_USERNAME']      ?? '',
    'password'     => $_ENV['MAIL_PASSWORD']      ?? '',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS']  ?? '',
    'from_name'    => $_ENV['MAIL_FROM_NAME']     ?? APP_NAME,
];

// Si estamos en localhost y no hay credenciales configuradas, intentar usar Mailtrap de prueba
if ($isLocal && empty($config['username'])) {
    // Usar Mailtrap sandbox por defecto en desarrollo
    $config['host'] = 'sandbox.smtp.mailtrap.io';
    $config['port'] = 2525;
    $config['username'] = $_ENV['MAILTRAP_USERNAME'] ?? '2c844e9ec0e60a';
    $config['password'] = $_ENV['MAILTRAP_PASSWORD'] ?? '6a6f25e7fccc4f';
    $config['from_address'] = $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@ejemplo.local';
}

return $config;
