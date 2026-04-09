<?php
/**
 * Middleware de rate limiting para formularios públicos
 * Bloquea IPs que superen el límite configurado de envíos
 */

class RateLimitMiddleware
{
    public function handle(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $ip = RateLimit::getClientIp();

        if (!RateLimit::check('form_submit', $ip)) {
            http_response_code(429);
            Session::flash('error', 'Enviaste demasiados formularios en poco tiempo. Por favor, esperá unos minutos antes de intentar nuevamente.');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? APP_URL));
            exit;
        }

        // Registrar el intento
        RateLimit::hit('form_submit', $ip);
    }
}
