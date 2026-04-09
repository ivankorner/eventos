<?php
/**
 * Helper para manejo de sesiones PHP
 * Centraliza inicio, escritura, lectura y destrucción de sesiones
 */

class Session
{
    private static bool $started = false;

    /**
     * Inicia la sesión si no está activa
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'domain'   => '',
            'secure'   => (APP_ENV === 'production'),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        session_start();
        self::$started = true;
    }

    /**
     * Guarda un valor en la sesión
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtiene un valor de la sesión
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Verifica si existe una clave en la sesión
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Elimina un valor de la sesión
     */
    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destruye completamente la sesión
     */
    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();

        // Eliminar la cookie de sesión
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        self::$started = false;
    }

    /**
     * Regenera el ID de sesión (usar en login para prevenir session fixation)
     */
    public static function regenerate(): void
    {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Guarda un mensaje flash (disponible solo en el próximo request)
     */
    public static function flash(string $key, mixed $value): void
    {
        self::start();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Obtiene y elimina un mensaje flash
     */
    public static function getFlash(string $key, mixed $default = null): mixed
    {
        self::start();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Verifica si hay un mensaje flash
     */
    public static function hasFlash(string $key): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$key]);
    }

    /**
     * Retorna el usuario logueado o null
     */
    public static function user(): ?array
    {
        return self::get('user');
    }

    /**
     * Verifica si hay un usuario logueado
     */
    public static function isLoggedIn(): bool
    {
        return self::has('user') && !empty(self::get('user')['id']);
    }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public static function hasRole(string|array $roles): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $roles = (array) $roles;
        return in_array($user['role'], $roles, true);
    }
}
