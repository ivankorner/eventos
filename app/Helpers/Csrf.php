<?php
/**
 * Helper CSRF — genera y verifica tokens para proteger formularios POST
 */

class Csrf
{
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Genera (o reutiliza) el token CSRF de la sesión
     */
    public static function token(): string
    {
        Session::start();

        if (!Session::has(self::TOKEN_KEY)) {
            Session::set(self::TOKEN_KEY, bin2hex(random_bytes(32)));
        }

        return Session::get(self::TOKEN_KEY);
    }

    /**
     * Retorna el campo HTML hidden con el token CSRF
     */
    public static function field(): string
    {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }

    /**
     * Verifica el token CSRF enviado en un POST
     * Lanza excepción si no es válido
     */
    public static function verify(): void
    {
        $submitted = $_POST['_csrf_token'] ?? '';
        $stored    = Session::get(self::TOKEN_KEY, '');

        if (empty($submitted) || !hash_equals($stored, $submitted)) {
            http_response_code(419);
            die('Token CSRF inválido. Por favor, recargá la página e intentá de nuevo.');
        }
    }

    /**
     * Verifica sin lanzar excepción — retorna bool
     */
    public static function isValid(): bool
    {
        $submitted = $_POST['_csrf_token'] ?? '';
        $stored    = Session::get(self::TOKEN_KEY, '');
        return !empty($submitted) && hash_equals($stored, $submitted);
    }

    /**
     * Regenera el token (útil para formularios de un solo uso)
     */
    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set(self::TOKEN_KEY, $token);
        return $token;
    }
}
