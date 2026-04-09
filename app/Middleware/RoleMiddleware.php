<?php
/**
 * Middleware de roles
 * Verifica que el usuario tenga el rol necesario para acceder a un recurso
 */

class RoleMiddleware
{
    private array $allowedRoles;

    public function __construct(array $roles = ['super_admin'])
    {
        $this->allowedRoles = $roles;
    }

    public function handle(array $params = []): void
    {
        $user = Session::user();

        if (!$user || !in_array($user['role'], $this->allowedRoles, true)) {
            http_response_code(403);
            Session::flash('error', 'No tenés permisos para acceder a esta sección.');
            header('Location: ' . APP_URL . '/admin/dashboard');
            exit;
        }
    }
}

/**
 * Middleware de solo Super Admin
 * Atajo para recursos exclusivos del super_admin
 */
class SuperAdminMiddleware extends RoleMiddleware
{
    public function __construct()
    {
        parent::__construct(['super_admin']);
    }
}
