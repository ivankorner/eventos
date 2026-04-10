<?php
/**
 * Controlador del constructor de formularios dinámicos
 */

class FormController
{
    private FormModel  $formModel;
    private EventModel $eventModel;

    public function __construct()
    {
        $this->formModel  = new FormModel();
        $this->eventModel = new EventModel();
    }

    /**
     * GET /admin/eventos/{id}/formulario
     */
    public function builder(array $params = []): void
    {
        $event = $this->findEventOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $form = $this->formModel->findByEvent($event['id']);

        // JSON del formulario para pasar al constructor JS
        $formJson = $form ? $form['fields_json'] : json_encode([
            'version'  => '1.0',
            'settings' => [
                'submit_label'     => 'Enviar inscripción',
                'success_message'  => '¡Gracias! Tu inscripción fue recibida.',
                'notify_email'     => $event['notify_email'] ?? '',
                'max_submissions'  => null,
                'allow_duplicates' => false,
            ],
            'fields' => [],
        ]);

        $this->render('admin/forms/builder', [
            'event'     => $event,
            'form'      => $form,
            'formJson'  => $formJson,
            'pageTitle' => 'Constructor de formulario — ' . $event['title'],
            'success'   => Session::getFlash('success'),
        ]);
    }

    /**
     * POST /admin/eventos/{id}/formulario/guardar
     * Guarda el formulario enviado por el constructor JS (vía AJAX o POST normal)
     */
    public function save(array $params = []): void
    {
        // Verificar CSRF — para AJAX, retornar JSON
        if (!Csrf::isValid()) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'Token CSRF inválido. Por favor, recargá la página.']);
            }
            http_response_code(419);
            die('Token CSRF inválido. Por favor, recargá la página e intentá de nuevo.');
        }

        $event = $this->findEventOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $rawJson  = $_POST['fields_json'] ?? '';
        $title    = trim($_POST['form_title'] ?? 'Formulario');
        $activate = isset($_POST['activate']);

        // Validar que el JSON sea válido
        $decoded = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            if ($this->isAjax()) {
                $this->jsonResponse(['success' => false, 'message' => 'El JSON del formulario es inválido.']);
            }
            Session::flash('error', 'El JSON del formulario es inválido.');
            header('Location: ' . APP_URL . '/admin/eventos/' . $event['id'] . '/formulario');
            exit;
        }

        $formId = $this->formModel->saveForEvent($event['id'], $title, $decoded, $activate);

        AuditLogModel::log('form.saved', 'Form', $formId, [
            'event_id' => $event['id'],
            'activate' => $activate,
        ]);

        if ($this->isAjax()) {
            $this->jsonResponse([
                'success'  => true,
                'message'  => 'Formulario guardado correctamente.',
                'form_id'  => $formId,
                'saved_at' => date('H:i:s'),
            ]);
        }

        Session::flash('success', 'Formulario guardado correctamente.');
        header('Location: ' . APP_URL . '/admin/eventos/' . $event['id'] . '/formulario');
        exit;
    }

    // -------------------------
    // Helpers privados
    // -------------------------

    private function findEventOrFail(int $id): array
    {
        $event = $this->eventModel->find($id);
        if (!$event || $event['deleted_at'] !== null) {
            http_response_code(404);
            die('Evento no encontrado.');
        }
        return $event;
    }

    private function authorizeEvent(array $event): void
    {
        $user = Session::user();
        if ($user['role'] !== 'super_admin' && $event['user_id'] !== $user['id']) {
            http_response_code(403);
            die('No tenés permisos para editar este formulario.');
        }
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
