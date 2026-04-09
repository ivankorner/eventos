<?php
/**
 * Controlador de configuración del sistema (solo super_admin)
 */

class SettingsController
{
    /**
     * GET /admin/configuracion
     */
    public function index(array $params = []): void
    {
        // Evitar caché del navegador
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');

        $db = Database::getInstance();
        $stmt = $db->query("SELECT key_name, value_data FROM settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key_name']] = $row['value_data'];
        }

        $this->render('admin/settings/index', [
            'settings'  => $settings,
            'csrf'      => Csrf::field(),
            'pageTitle' => 'Configuración del sistema',
            'success'   => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    /**
     * POST /admin/configuracion
     */
    public function update(array $params = []): void
    {
        Csrf::verify();

        $db     = Database::getInstance();
        $fields = ['app_name', 'hero_title', 'hero_subtitle', 'footer_text'];

        foreach ($fields as $key) {
            if (isset($_POST[$key])) {
                $db->prepare(
                    "INSERT INTO settings (key_name, value_data) VALUES (:key, :val)
                     ON DUPLICATE KEY UPDATE value_data = :val2"
                )->execute([
                    ':key'  => $key,
                    ':val'  => trim($_POST[$key]),
                    ':val2' => trim($_POST[$key]),
                ]);
            }
        }

        // Procesar logo si se subió
        if (!empty($_FILES['app_logo']['name'])) {
            try {
                $uploaded = Upload::process($_FILES['app_logo'], 'system', Upload::imageMimes(), 1024 * 1024);
                $db->prepare(
                    "INSERT INTO settings (key_name, value_data) VALUES ('app_logo', :val)
                     ON DUPLICATE KEY UPDATE value_data = :val2"
                )->execute([':val' => $uploaded['path'], ':val2' => $uploaded['path']]);
            } catch (\RuntimeException $e) {
                Session::flash('error', $e->getMessage());
                header('Location: ' . APP_URL . '/admin/configuracion');
                exit;
            }
        }

        AuditLogModel::log('settings.updated', 'Settings');

        Session::flash('success', 'Configuración guardada correctamente.');
        header('Location: ' . APP_URL . '/admin/configuracion');
        exit;
    }

    /**
     * POST /admin/configuracion/email-prueba
     */
    public function testEmail(array $params = []): void
    {
        Csrf::verify();

        $toEmail = trim($_POST['test_email'] ?? '');
        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Ingresá un email válido para la prueba.');
            header('Location: ' . APP_URL . '/admin/configuracion');
            exit;
        }

        try {
            Email::send(
                $toEmail,
                'Destinatario de prueba',
                'Email de prueba — ' . ConfigHelper::getAppName(),
                '<h2>Email de prueba</h2><p>Si recibís este email, la configuración SMTP es correcta.</p><p>— ' . ConfigHelper::getAppName() . '</p>'
            );
            Session::flash('success', "Email de prueba enviado a {$toEmail} correctamente.");
        } catch (\RuntimeException $e) {
            Session::flash('error', 'Error al enviar el email: ' . $e->getMessage());
        }

        header('Location: ' . APP_URL . '/admin/configuracion');
        exit;
    }

    /**
     * GET /admin/configuracion/audit-log
     */
    public function auditLog(array $params = []): void
    {
        $search  = trim($_GET['q'] ?? '');
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 25;

        $auditModel = new AuditLogModel();
        $total      = $auditModel->countAll($search);
        $pag        = new Paginator($total, $perPage, $page);
        $logs       = $auditModel->getPaginated($pag->limit(), $pag->offset(), $search);

        $this->render('admin/settings/audit-log', [
            'logs'      => $logs,
            'paginator' => $pag,
            'search'    => $search,
            'pageTitle' => 'Log de auditoría',
        ]);
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
