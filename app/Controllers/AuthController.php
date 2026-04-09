<?php
/**
 * Controlador de autenticación
 * Maneja login, logout y cambio de contraseña
 */

class AuthController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * GET /admin/login
     */
    public function loginForm(array $params = []): void
    {
        // Si ya está logueado, redirigir al dashboard
        if (Session::isLoggedIn()) {
            header('Location: ' . APP_URL . '/admin/dashboard');
            exit;
        }

        $this->render('auth/login', [
            'csrf'  => Csrf::field(),
            'error' => Session::getFlash('error'),
            'info'  => Session::getFlash('info'),
        ]);
    }

    /**
     * POST /admin/login
     */
    public function login(array $params = []): void
    {
        Csrf::verify();

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $ip       = RateLimit::getClientIp();
        $ua       = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Verificar bloqueo por intentos fallidos
        if ($this->userModel->isLocked($email, $ip)) {
            Session::flash('error', 'Tu cuenta está bloqueada temporalmente por demasiados intentos fallidos. Intentá en 15 minutos.');
            header('Location: ' . APP_URL . '/admin/login');
            exit;
        }

        // Buscar usuario
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->userModel->recordFailedAttempt($email, $ip);
            Session::flash('error', 'Email o contraseña incorrectos.');
            header('Location: ' . APP_URL . '/admin/login');
            exit;
        }

        // Verificar que la cuenta esté activa
        if (!$user['is_active']) {
            Session::flash('error', 'Tu cuenta está desactivada. Contactá al administrador.');
            header('Location: ' . APP_URL . '/admin/login');
            exit;
        }

        // Login exitoso
        $this->userModel->clearLoginAttempts($email);
        $this->userModel->updateLastLogin($user['id']);

        // Regenerar sesión para prevenir session fixation
        Session::regenerate();

        // Guardar datos del usuario en sesión (sin el password)
        unset($user['password']);
        Session::set('user', $user);

        // Registrar sesión en BD
        $token = bin2hex(random_bytes(32));
        $this->userModel->createSession($user['id'], $token, $ip, $ua);

        AuditLogModel::log('user.login', 'User', $user['id'], ['ip' => $ip]);

        // Si debe cambiar password, redirigir ahí
        if ($user['must_change_password']) {
            header('Location: ' . APP_URL . '/admin/cambiar-password');
            exit;
        }

        header('Location: ' . APP_URL . '/admin/dashboard');
        exit;
    }

    /**
     * GET /admin/logout
     */
    public function logout(array $params = []): void
    {
        $user = Session::user();
        if ($user) {
            AuditLogModel::log('user.logout', 'User', $user['id']);
        }

        Session::destroy();
        header('Location: ' . APP_URL . '/admin/login');
        exit;
    }

    /**
     * GET /admin/cambiar-password
     */
    public function changePasswordForm(array $params = []): void
    {
        $this->render('auth/change-password', [
            'csrf'  => Csrf::field(),
            'error' => Session::getFlash('error'),
            'forced' => (bool) (Session::user()['must_change_password'] ?? false),
        ]);
    }

    /**
     * POST /admin/cambiar-password
     */
    public function changePassword(array $params = []): void
    {
        Csrf::verify();

        $user        = Session::user();
        $currentPass = $_POST['current_password'] ?? '';
        $newPass     = $_POST['new_password'] ?? '';
        $confirm     = $_POST['new_password_confirmation'] ?? '';

        // Validaciones
        $v = new Validator($_POST);
        $v->required(['current_password', 'new_password', 'new_password_confirmation'])
          ->minLength('new_password', 8, 'nueva contraseña')
          ->confirmed('new_password', 'nueva contraseña');

        // Verificar contraseña actual
        $dbUser = $this->userModel->find($user['id']);
        if ($dbUser && !password_verify($currentPass, $dbUser['password'])) {
            $v->addError('current_password', 'La contraseña actual es incorrecta.');
        }

        if ($v->fails()) {
            Session::flash('error', array_values($v->errors())[0][0]);
            header('Location: ' . APP_URL . '/admin/cambiar-password');
            exit;
        }

        $hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->userModel->update($user['id'], [
            'password'             => $hash,
            'must_change_password' => 0,
        ]);

        // Actualizar sesión
        $updatedUser = array_merge($user, ['must_change_password' => 0]);
        Session::set('user', $updatedUser);

        AuditLogModel::log('user.password_changed', 'User', $user['id']);

        Session::flash('info', 'Contraseña actualizada correctamente.');
        header('Location: ' . APP_URL . '/admin/dashboard');
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        include VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
    }
}
