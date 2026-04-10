<?php
/**
 * Configuración de correo electrónico — servidor appcde.online
 */

return [
    'driver'       => $_ENV['MAIL_DRIVER']       ?? 'smtp',
    'host'         => $_ENV['MAIL_HOST']          ?? 'appcde.online',
    'port'         => (int)($_ENV['MAIL_PORT']    ?? 587),
    'encryption'   => $_ENV['MAIL_ENCRYPTION']    ?? 'tls',
    'username'     => $_ENV['MAIL_USERNAME']      ?? '',
    'password'     => $_ENV['MAIL_PASSWORD']      ?? '',
    'from_address' => $_ENV['MAIL_FROM_ADDRESS']  ?? '',
    'from_name'    => $_ENV['MAIL_FROM_NAME']     ?? ($_ENV['APP_NAME'] ?? 'Sistema de Inscripciones'),
];
