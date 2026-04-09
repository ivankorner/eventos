<?php
/**
 * Controlador CRUD de eventos
 */

class EventController
{
    private EventModel $eventModel;

    public function __construct()
    {
        $this->eventModel = new EventModel();
    }

    /**
     * GET /admin/eventos
     */
    public function index(array $params = []): void
    {
        $user     = Session::user();
        $isSuperAdmin = $user['role'] === 'super_admin';

        $status   = $_GET['status'] ?? '';
        $search   = trim($_GET['q'] ?? '');
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $perPage  = 20;

        // Los admins solo ven sus propios eventos
        $filterUserId = $isSuperAdmin ? null : $user['id'];

        $total  = $this->eventModel->countAdmin($status ?: null, $filterUserId, $search);
        $pag    = new Paginator($total, $perPage, $page);
        $events = $this->eventModel->getAdminList($pag->limit(), $pag->offset(), $status ?: null, $filterUserId, $search);

        $this->render('admin/events/index', [
            'events'    => $events,
            'paginator' => $pag,
            'status'    => $status,
            'search'    => $search,
            'pageTitle' => 'Eventos',
            'success'   => Session::getFlash('success'),
            'error'     => Session::getFlash('error'),
        ]);
    }

    /**
     * GET /admin/eventos/crear
     */
    public function create(array $params = []): void
    {
        $this->render('admin/events/form', [
            'event'     => null,
            'csrf'      => Csrf::field(),
            'pageTitle' => 'Crear evento',
            'errors'    => Session::getFlash('errors') ?? [],
            'old'       => Session::getFlash('old') ?? [],
        ]);
    }

    /**
     * POST /admin/eventos/crear
     */
    public function store(array $params = []): void
    {
        Csrf::verify();

        $data = $this->extractEventData();
        $v    = $this->validateEventData($data);

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Session::flash('old', $_POST);
            header('Location: ' . APP_URL . '/admin/eventos/crear');
            exit;
        }

        $user         = Session::user();
        $data['slug'] = Slug::unique($data['slug'] ?: $data['title']);
        $data['user_id'] = $user['id'];

        // Procesar imagen de portada si se subió
        if (!empty($_FILES['cover_image']['name'])) {
            try {
                $uploaded = Upload::process($_FILES['cover_image'], 'events', Upload::imageMimes(), 20 * 1024 * 1024);
                $data['cover_image'] = $uploaded['path'];
            } catch (\RuntimeException $e) {
                Session::flash('errors', ['cover_image' => [$e->getMessage()]]);
                Session::flash('old', $_POST);
                header('Location: ' . APP_URL . '/admin/eventos/crear');
                exit;
            }
        }

        $id = $this->eventModel->insert($data);
        AuditLogModel::log('event.created', 'Event', $id, ['title' => $data['title']]);

        Session::flash('success', 'Evento creado correctamente.');
        header('Location: ' . APP_URL . '/admin/eventos/' . $id . '/editar');
        exit;
    }

    /**
     * GET /admin/eventos/{id}/editar
     */
    public function edit(array $params = []): void
    {
        $event = $this->findOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $this->render('admin/events/form', [
            'event'     => $event,
            'csrf'      => Csrf::field(),
            'pageTitle' => 'Editar evento: ' . $event['title'],
            'errors'    => Session::getFlash('errors') ?? [],
            'old'       => Session::getFlash('old') ?? [],
            'success'   => Session::getFlash('success'),
        ]);
    }

    /**
     * POST /admin/eventos/{id}/editar
     */
    public function update(array $params = []): void
    {
        Csrf::verify();

        $event = $this->findOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $data = $this->extractEventData();
        $v    = $this->validateEventData($data);

        if ($v->fails()) {
            Session::flash('errors', $v->errors());
            Session::flash('old', $_POST);
            header('Location: ' . APP_URL . '/admin/eventos/' . $event['id'] . '/editar');
            exit;
        }

        // Slug único excluyendo el evento actual
        $data['slug'] = Slug::unique($data['slug'] ?: $data['title'], 'events', $event['id']);

        // Procesar nueva imagen si se subió
        if (!empty($_FILES['cover_image']['name'])) {
            try {
                $uploaded = Upload::process($_FILES['cover_image'], 'events', Upload::imageMimes(), 20 * 1024 * 1024);
                // Eliminar imagen anterior si existe
                if ($event['cover_image']) {
                    Upload::delete($event['cover_image']);
                }
                $data['cover_image'] = $uploaded['path'];
            } catch (\RuntimeException $e) {
                Session::flash('errors', ['cover_image' => [$e->getMessage()]]);
                Session::flash('old', $_POST);
                header('Location: ' . APP_URL . '/admin/eventos/' . $event['id'] . '/editar');
                exit;
            }
        } else {
            // Mantener imagen existente
            unset($data['cover_image']);
        }

        $this->eventModel->update($event['id'], $data);
        AuditLogModel::log('event.updated', 'Event', $event['id'], ['title' => $data['title']]);

        Session::flash('success', 'Evento actualizado correctamente.');
        header('Location: ' . APP_URL . '/admin/eventos/' . $event['id'] . '/editar');
        exit;
    }

    /**
     * POST /admin/eventos/{id}/eliminar
     */
    public function destroy(array $params = []): void
    {
        Csrf::verify();

        $event = $this->findOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $this->eventModel->softDelete($event['id']);
        AuditLogModel::log('event.deleted', 'Event', $event['id'], ['title' => $event['title']]);

        Session::flash('success', 'Evento eliminado.');
        header('Location: ' . APP_URL . '/admin/eventos');
        exit;
    }

    /**
     * POST /admin/eventos/{id}/duplicar
     */
    public function duplicate(array $params = []): void
    {
        Csrf::verify();

        $event = $this->findOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $user  = Session::user();
        $newId = $this->eventModel->duplicate($event['id'], $user['id']);
        AuditLogModel::log('event.duplicated', 'Event', $newId, ['from_id' => $event['id']]);

        Session::flash('success', 'Evento duplicado como borrador.');
        header('Location: ' . APP_URL . '/admin/eventos/' . $newId . '/editar');
        exit;
    }

    /**
     * POST /admin/eventos/{id}/estado
     */
    public function changeStatus(array $params = []): void
    {
        Csrf::verify();

        $event  = $this->findOrFail((int)$params['id']);
        $this->authorizeEvent($event);

        $status = $_POST['status'] ?? '';
        if (!in_array($status, ['draft', 'published', 'finished'], true)) {
            Session::flash('error', 'Estado inválido.');
            header('Location: ' . APP_URL . '/admin/eventos');
            exit;
        }

        $this->eventModel->update($event['id'], ['status' => $status]);
        AuditLogModel::log('event.status_changed', 'Event', $event['id'], ['status' => $status]);

        Session::flash('success', 'Estado del evento actualizado.');
        header('Location: ' . APP_URL . '/admin/eventos');
        exit;
    }

    // -------------------------
    // Helpers privados
    // -------------------------

    private function extractEventData(): array
    {
        return [
            'title'            => trim($_POST['title'] ?? ''),
            'slug'             => Slug::generate(trim($_POST['slug'] ?? '')),
            'description'      => $_POST['description'] ?? '',
            'location'         => trim($_POST['location'] ?? ''),
            'start_date'       => $_POST['start_date'] ?? null,
            'end_date'         => $_POST['end_date'] ?? null,
            'max_capacity'     => !empty($_POST['max_capacity']) ? (int)$_POST['max_capacity'] : null,
            'status'           => $_POST['status'] ?? 'draft',
            'visibility'       => $_POST['visibility'] ?? 'public',
            'notify_email'     => trim($_POST['notify_email'] ?? ''),
            'meta_description' => trim($_POST['meta_description'] ?? ''),
        ];
    }

    private function validateEventData(array $data): Validator
    {
        $v = new Validator($data);
        $v->required('title', 'título')
          ->maxLength('title', 255, 'título')
          ->email('notify_email', 'email de notificación')
          ->in('status', ['draft', 'published', 'finished'], 'estado')
          ->in('visibility', ['public', 'private'], 'visibilidad');

        return $v;
    }

    private function findOrFail(int $id): array
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
            die('No tenés permisos para editar este evento.');
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        $content = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        include VIEWS_PATH . '/layouts/admin.php';
    }
}
