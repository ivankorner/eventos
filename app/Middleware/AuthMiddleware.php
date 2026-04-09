<?php
/**
 * Middleware de autenticación
 * Verifica que haya una sesión activa antes de acceder al área admin
 */

class AuthMiddleware
{
    public function handle(array $params = []): void
    {
        Session::start();

        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Debés iniciar sesión para acceder a esta sección.');
            header('Location: ' . APP_URL . '/admin/login');
            exit;
        }

        // Si el usuario debe cambiar la contraseña, redirigir a ese formulario
        $user = Session::user();
        if (
            !empty($user['must_change_password']) &&
            !str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/cambiar-password')
        ) {
            header('Location: ' . APP_URL . '/admin/cambiar-password');
            exit;
        }
    }
}
