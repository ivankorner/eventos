<?php
/**
 * Controlador de inscripciones (panel admin)
 */

class SubmissionController
{
    private SubmissionModel $subModel;
    private EventModel      $eventModel;
    private FormModel       $formModel;

    public function __construct()
    {
        $this->subModel   = new SubmissionModel();
        $this->eventModel = new EventModel();
        $this->formModel  = new FormModel();
    }

    /**
     * GET /admin/eventos/{id}/inscripciones
     */
    public function index(array $params = []): void
    {
        $event = $this->findEventOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $status   = $_GET['status']    ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo   = $_GET['date_to']   ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 25;

        $total   = $this->subModel->countByEvent($event['id'], $status, $dateFrom, $dateTo);
        $pag     = new Paginator($total, $perPage, $page);
        $subs    = $this->subModel->getByEvent($event['id'], $pag->limit(), $pag->offset(), $status, '', $dateFrom, $dateTo);

        // Obtener campos del formulario para las cabeceras de la tabla
        $form       = $this->formModel->findByEvent($event['id']);
        $formFields = $form ? $this->formModel->getFields($form['id'], ['heading', 'paragraph', 'file']) : [];

        $this->render('admin/submissions/index', [
            'event'       => $event,
            'submissions' => $subs,
            'formFields'  => $formFields,
            'paginator'   => $pag,
            'status'      => $status,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'pageTitle'   => 'Inscripciones — ' . $event['title'],
            'success'     => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    /**
     * GET /admin/inscripciones
     * Lista todas las inscripciones de todos los eventos
     */
    public function allSubmissions(array $params = []): void
    {
        $user     = Session::user();
        $status   = $_GET['status']    ?? '';
        $dateFrom = $_GET['date_from'] ?? '';
        $dateTo   = $_GET['date_to']   ?? '';
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 25;

        // Si no es super_admin, solo muestra sus eventos
        $userId = $user['role'] === 'super_admin' ? null : $user['id'];

        $total   = $this->subModel->countAll($status, $dateFrom, $dateTo, $userId);
        $pag     = new Paginator($total, $perPage, $page);
        $subs    = $this->subModel->getAll($pag->limit(), $pag->offset(), $status, $dateFrom, $dateTo, $userId);

        $this->render('admin/submissions/all', [
            'submissions' => $subs,
            'paginator'   => $pag,
            'status'      => $status,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'pageTitle'   => 'Todas las inscripciones',
            'success'     => Session::getFlash('success'),
            'error'       => Session::getFlash('error'),
        ]);
    }

    /**
     * GET /admin/inscripciones/{id}
     */
    public function show(array $params = []): void
    {
        $sub = $this->findSubOrFail((int)$params['id']);

        $event      = $this->eventModel->find($sub['event_id']);
        $form       = $this->formModel->findByEvent($sub['event_id']);
        $formFields = $form ? $this->formModel->getFields($form['id'], []) : [];

        $this->render('admin/submissions/show', [
            'submission' => $sub,
            'event'      => $event,
            'formFields' => $formFields,
            'csrf'       => Csrf::field(),
            'pageTitle'  => 'Detalle de inscripción #' . $sub['id'],
            'success'    => Session::getFlash('success'),
        ]);
    }

    /**
     * POST /admin/inscripciones/{id}/estado
     */
    public function updateStatus(array $params = []): void
    {
        Csrf::verify();

        $sub    = $this->findSubOrFail((int)$params['id']);
        $status = $_POST['status'] ?? '';

        if (!in_array($status, ['pending', 'confirmed', 'cancelled'], true)) {
            Session::flash('error', 'Estado inválido.');
            header('Location: ' . APP_URL . '/admin/inscripciones/' . $sub['id']);
            exit;
        }

        $this->subModel->updateStatus($sub['id'], $status);
        AuditLogModel::log('submission.status_changed', 'Submission', $sub['id'], ['status' => $status]);

        Session::flash('success', 'Estado actualizado.');
        header('Location: ' . APP_URL . '/admin/inscripciones/' . $sub['id']);
        exit;
    }

    /**
     * POST /admin/inscripciones/{id}/eliminar
     */
    public function destroy(array $params = []): void
    {
        Csrf::verify();

        $sub = $this->findSubOrFail((int)$params['id']);
        $this->subModel->softDelete($sub['id']);

        AuditLogModel::log('submission.deleted', 'Submission', $sub['id']);

        Session::flash('success', 'Inscripción eliminada.');
        header('Location: ' . APP_URL . '/admin/eventos/' . $sub['event_id'] . '/inscripciones');
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

    private function findSubOrFail(int $id): array
    {
        $sub = $this->subModel->findWithData($id);
        if (!$sub || $sub['deleted_at'] !== null) {
            http_response_code(404);
            die('Inscripción no encontrada.');
        }
        return $sub;
    }

    private function authorizeEvent(array $event): void
    {
        $user = Session::user();
        if ($user['role'] === 'editor') return; // Los editors solo ven
        if ($user['role'] !== 'super_admin' && $event['user_id'] !== $user['id']) {
            http_response_code(403);
            die('No tenés permisos para ver estas inscripciones.');
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
