<?php
/**
 * Manejo centralizado de errores
 * En desarrollo: muestra el error en pantalla
 * En producción: loguea a /storage/logs/errors.log y muestra página genérica
 */

class ErrorHandler
{
    /**
     * Registra los handlers globales de errores y excepciones
     */
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Maneja errores PHP (E_WARNING, E_NOTICE, etc.)
     */
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Ignorar errores suprimidos con @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $message = "[{$errno}] {$errstr} en {$errfile}:{$errline}";
        self::log($message);

        if (APP_DEBUG) {
            echo "<pre style='background:#fee;padding:10px;border:1px solid red'><b>Error PHP:</b>\n{$message}</pre>";
        }

        // Devolver true para no ejecutar el handler interno de PHP
        return true;
    }

    /**
     * Maneja excepciones no capturadas
     */
    public static function handleException(\Throwable $e): void
    {
        $message = sprintf(
            "[EXCEPTION] %s: %s en %s:%d\nStack trace:\n%s",
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        self::log($message);

        http_response_code(500);

        if (APP_DEBUG) {
            echo "<pre style='background:#fee;padding:10px;border:1px solid red'>";
            echo "<b>Excepción no capturada:</b>\n";
            echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
            echo "</pre>";
        } else {
            include VIEWS_PATH . '/errors/500.php';
        }
    }

    /**
     * Captura errores fatales en el shutdown
     */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $message = "[FATAL] {$error['message']} en {$error['file']}:{$error['line']}";
            self::log($message);

            if (!APP_DEBUG) {
                http_response_code(500);
                include VIEWS_PATH . '/errors/500.php';
            }
        }
    }

    /**
     * Escribe un mensaje en el log de errores
     */
    public static function log(string $message): void
    {
        $logFile = STORAGE_PATH . '/logs/errors.log';
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';
        $line = "[{$timestamp}] [{$ip}] [{$uri}] {$message}\n";

        // Crear el directorio si no existe
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Escribe en el log de aplicación general
     */
    public static function logApp(string $message, string $level = 'INFO'): void
    {
        $logFile = STORAGE_PATH . '/logs/app.log';
        $timestamp = date('Y-m-d H:i:s');
        $line = "[{$timestamp}] [{$level}] {$message}\n";

        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
    }
}
