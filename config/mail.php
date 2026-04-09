<?php
/**
 * Configuración de correo electrónico
 * Retorna el array de configuración SMTP para el helper Email
 */
return [
    'driver'       => $_ENV['MAIL_DRIVER']       ?? 'smtp',
    'host'         => $_ENV['MAIL_HOST']          ?? 'smtp.gmail.com',
    'port'         => (int)($_ENV['MAIL_PORT']    ?? 587),
    'encryption'   => $_ENV['MAIL_ENCRYPTION']    ?? 'tls',
    'username'     => $_ENV['MAIL_USERNAME']      ?? '',
    'password'     => $_ENV['MAIL_PASSWORD']      ?? '',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS']  ?? '',
    'from_name'    => $_ENV['MAIL_FROM_NAME']     ?? APP_NAME,
];
