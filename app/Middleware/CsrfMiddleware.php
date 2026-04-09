<?php
/**
 * Middleware CSRF
 * Verifica el token en todos los requests POST
 */

class CsrfMiddleware
{
    public function handle(array $params = []): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
        }
    }
}
