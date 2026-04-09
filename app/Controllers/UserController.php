<?php
/**
 * Controlador de gestión de usuarios (solo super_admin)
 */

class UserController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * GET /admin/usuarios
     */
    public function index(array $params = []): void
    {
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;

        $total = $this->userModel->count();
        $pag   = new Paginator($total, $perPage, $page);

        $stmt = Database::getInstance()->prepare(
            "SELECT id, name, email, role, is_active, must_change_password, last_login_at, created_at
             FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset"
        );
        $stmt->bindValue(':limit',  $pag->limit(),  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $pag->offset(), PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();

        $this->render('admin/users/index', [
            'users'     => $users,
            'paginator' => $pag,
            'pageTitle' => 'Usuarios',
            'success'   => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    /**
     * GET /admin/usuarios/crear
     */
    public function create(array $params = []): void
    {
        $this->render('admin/users/form', [
            'user'      => null,
            'csrf'      => Csrf::field(),
            'pageTitle' => 'Crear usuario',
            'errors'    => Session::getFlash('errors') ?? [],
        ]);
    }

    /**
     * POST /admin/usuarios/crear
     */
    public function store(array $params = []): void
    {
        Csrf::verify();

        $v = new Validator($_POST);
        $v->required(['name', 'email', 'role'])
          ->email('email', 'email')
          ->maxLength('name', 100, 'nombre')
          ->in('role', ['super_admin', 'admin', 'editor'], 'rol');

        // Verificar que el email no esté en uso
        if ($this->userModel->findByEmail(trim($_POST['email'] ?? ''))) {
            $v->addError('email', 'Este email ya está registrado en el sistema.');
        }

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            header('Location: ' . APP_URL . '/admin/usuarios/crear');
            exit;
        }

        $tempPassword = UserModel::generateTempPassword();
        $hash         = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        $creator      = Session::user();

        $userId = $this->userModel->insert([
            'name'                 => trim($_POST['name']),
            'email'                => trim($_POST['email']),
            'password'             => $hash,
            'role'                 => $_POST['role'],
            'is_active'            => 1,
            'must_change_password' => 1,
            'created_by'           => $creator['id'],
        ]);

        // Enviar email de bienvenida con la contraseña temporal
        try {
            $newUser = $this->userModel->find($userId);
            $html    = Email::buildWelcomeHtml($newUser, $tempPassword);
            Email::queue(
                $newUser['email'],
                $newUser['name'],
                'Bienvenido/a a ' . ConfigHelper::getAppName(),
                $html
            );
        } catch (\Throwable $e) {
            ErrorHandler::log('Error encolando email de bienvenida: ' . $e->getMessage());
        }

        AuditLogModel::log('user.created', 'User', $userId, ['email' => $_POST['email']]);

        Session::flash('success', "Usuario creado. La contraseña temporal es: <code>{$tempPassword}</code> (también se envió por email).");
        header('Location: ' . APP_URL . '/admin/usuarios');
        exit;
    }

    /**
     * POST /admin/usuarios/{id}/activar
     */
    public function toggleActive(array $params = []): void
    {
        Csrf::verify();

        $id   = (int)$params['id'];
        $user = Session::user();

        // No puede desactivarse a sí mismo
        if ($id === $user['id']) {
            Session::flash('error', 'No podés desactivar tu propia cuenta.');
            header('Location: ' . APP_URL . '/admin/usuarios');
            exit;
        }

        $this->userModel->toggleActive($id);
        AuditLogModel::log('user.toggled', 'User', $id);

        Session::flash('success', 'Estado del usuario actualizado.');
        header('Location: ' . APP_URL . '/admin/usuarios');
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
