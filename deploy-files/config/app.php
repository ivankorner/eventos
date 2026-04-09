<?php
/**
 * Configuración global de la aplicación
 * Lee el archivo .env y define constantes globales
 */

// Huso horario Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Definir ruta base del proyecto (un nivel arriba de /config)
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('VIEWS_PATH', BASE_PATH . '/app/Views');

// Cargar variables de entorno desde .env
if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

// Constantes de la aplicación
define('APP_NAME',  $_ENV['APP_NAME']  ?? 'Sistema de Inscripciones');
define('APP_ENV',   $_ENV['APP_ENV']   ?? 'production');
define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));
define('APP_URL',   rtrim($_ENV['APP_URL'] ?? '', '/'));
define('APP_KEY',   $_ENV['APP_KEY']   ?? '');

// Configuración de sesión
define('SESSION_NAME',     $_ENV['SESSION_NAME']     ?? 'insc_session');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 7200));

// Configuración de uploads
define('UPLOAD_MAX_SIZE',      (int)($_ENV['UPLOAD_MAX_SIZE']      ?? 5242880));
define('UPLOAD_ALLOWED_TYPES', $_ENV['UPLOAD_ALLOWED_TYPES']       ?? 'pdf,jpg,jpeg,png,webp');
define('UPLOAD_PATH',          BASE_PATH . '/public/uploads');

// Rate limiting
define('RATE_LIMIT_SUBMISSIONS', (int)($_ENV['RATE_LIMIT_SUBMISSIONS'] ?? 5));
define('RATE_LIMIT_WINDOW',      (int)($_ENV['RATE_LIMIT_WINDOW']      ?? 3600));

// Configurar manejo de errores según el entorno
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
    ini_set('error_log', STORAGE_PATH . '/logs/errors.log');
}

// Configurar sesiones PHP de forma segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');
// En producción con HTTPS, activar cookie_secure
if (APP_ENV === 'production' && isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
