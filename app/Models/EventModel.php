<?php
/**
 * Modelo de eventos
 */

class EventModel extends BaseModel
{
    protected string $table = 'events';

    /**
     * Eventos publicados (página pública), con paginación
     */
    public function getPublished(int $limit, int $offset): array
    {
        return $this->query(
            "SELECT e.*,
                    (SELECT COUNT(*) FROM submissions s WHERE s.event_id = e.id AND s.deleted_at IS NULL) AS total_submissions,
                    (SELECT COUNT(*) FROM forms f WHERE f.event_id = e.id AND f.is_active = 1 LIMIT 1) AS has_form
             FROM events e
             WHERE e.status = 'published' AND e.deleted_at IS NULL
             ORDER BY e.start_date ASC
             LIMIT :limit OFFSET :offset",
            [':limit' => $limit, ':offset' => $offset]
        );
    }

    /**
     * Total de eventos publicados (para paginador)
     */
    public function countPublished(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM events WHERE status = 'published' AND deleted_at IS NULL"
        )->fetchColumn();
    }

    /**
     * Busca un evento por slug (para la página pública)
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->queryOne(
            "SELECT * FROM events WHERE slug = :slug AND deleted_at IS NULL LIMIT 1",
            [':slug' => $slug]
        );
    }

    /**
     * Eventos del panel admin con paginación y filtros
     */
    public function getAdminList(int $limit, int $offset, ?string $status = null, ?int $userId = null, string $search = ''): array
    {
        $where  = ['e.deleted_at IS NULL'];
        $binds  = [];

        if ($status) {
            $where[]         = 'e.status = :status';
            $binds[':status'] = $status;
        }

        // Los admins solo ven sus propios eventos; super_admin ve todos
        if ($userId) {
            $where[]           = 'e.user_id = :user_id';
            $binds[':user_id'] = $userId;
        }

        if ($search !== '') {
            $where[]         = 'e.title LIKE :search';
            $binds[':search'] = '%' . $search . '%';
        }

        $whereStr = implode(' AND ', $where);

        return $this->query(
            "SELECT e.*,
                    u.name AS author_name,
                    (SELECT COUNT(*) FROM submissions s WHERE s.event_id = e.id AND s.deleted_at IS NULL) AS total_submissions
             FROM events e
             LEFT JOIN users u ON u.id = e.user_id
             WHERE {$whereStr}
             ORDER BY e.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($binds, [':limit' => $limit, ':offset' => $offset])
        );
    }

    public function countAdmin(?string $status = null, ?int $userId = null, string $search = ''): int
    {
        $where = ['deleted_at IS NULL'];
        $binds = [];

        if ($status) {
            $where[]          = 'status = :status';
            $binds[':status'] = $status;
        }
        if ($userId) {
            $where[]           = 'user_id = :user_id';
            $binds[':user_id'] = $userId;
        }
        if ($search !== '') {
            $where[]          = 'title LIKE :search';
            $binds[':search'] = '%' . $search . '%';
        }

        $whereStr = implode(' AND ', $where);
        $stmt     = $this->db->prepare("SELECT COUNT(*) FROM events WHERE {$whereStr}");
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Verifica si el cupo está agotado
     */
    public function isFull(int $eventId): bool
    {
        $event = $this->find($eventId);
        if (!$event || !$event['max_capacity']) {
            return false;
        }

        $count = (int) $this->db->prepare(
            "SELECT COUNT(*) FROM submissions WHERE event_id = :id AND deleted_at IS NULL"
        )->execute([':id' => $eventId]) ? $this->db->query(
            "SELECT COUNT(*) FROM submissions WHERE event_id = {$eventId} AND deleted_at IS NULL"
        )->fetchColumn() : 0;

        return $count >= $event['max_capacity'];
    }

    /**
     * Métricas para el dashboard
     */
    public function getDashboardMetrics(): array
    {
        return [
            'active_events'   => (int) $this->db->query("SELECT COUNT(*) FROM events WHERE status = 'published' AND deleted_at IS NULL")->fetchColumn(),
            'total_events'    => (int) $this->db->query("SELECT COUNT(*) FROM events WHERE deleted_at IS NULL")->fetchColumn(),
            'total_submissions' => (int) $this->db->query("SELECT COUNT(*) FROM submissions WHERE deleted_at IS NULL")->fetchColumn(),
            'week_submissions'  => (int) $this->db->query("SELECT COUNT(*) FROM submissions WHERE deleted_at IS NULL AND submitted_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn(),
            'expiring_soon'   => $this->query(
                "SELECT id, title, slug, end_date FROM events WHERE status = 'published' AND deleted_at IS NULL AND end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 3 DAY)",
                []
            ),
        ];
    }

    /**
     * Datos del gráfico de inscripciones (últimos 30 días)
     */
    public function getSubmissionsChart(): array
    {
        return $this->query(
            "SELECT DATE(submitted_at) AS day, COUNT(*) AS total
             FROM submissions
             WHERE deleted_at IS NULL AND submitted_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(submitted_at)
             ORDER BY day ASC",
            []
        );
    }

    /**
     * Últimas N inscripciones (para el dashboard)
     */
    public function getRecentSubmissions(int $limit = 10): array
    {
        return $this->query(
            "SELECT s.*, e.title AS event_title, e.slug AS event_slug
             FROM submissions s
             JOIN events e ON e.id = s.event_id
             WHERE s.deleted_at IS NULL
             ORDER BY s.submitted_at DESC
             LIMIT :limit",
            [':limit' => $limit]
        );
    }

    /**
     * Duplica un evento (sin inscripciones), retorna el ID del nuevo evento
     */
    public function duplicate(int $eventId, int $userId): int
    {
        $event = $this->find($eventId);
        if (!$event) {
            throw new \RuntimeException('Evento no encontrado.');
        }

        $newSlug = Slug::unique($event['slug'] . '-copia');

        $newId = $this->insert([
            'user_id'          => $userId,
            'title'            => $event['title'] . ' (copia)',
            'slug'             => $newSlug,
            'description'      => $event['description'],
            'cover_image'      => $event['cover_image'],
            'location'         => $event['location'],
            'start_date'       => $event['start_date'],
            'end_date'         => $event['end_date'],
            'max_capacity'     => $event['max_capacity'],
            'status'           => 'draft',
            'notify_email'     => $event['notify_email'],
            'meta_description' => $event['meta_description'],
        ]);

        // Duplicar también el formulario asociado si existe
        $form = $this->queryOne(
            "SELECT * FROM forms WHERE event_id = :eid ORDER BY id DESC LIMIT 1",
            [':eid' => $eventId]
        );

        if ($form) {
            $this->execute(
                "INSERT INTO forms (event_id, title, fields_json, is_active)
                 VALUES (:eid, :title, :json, 1)",
                [
                    ':eid'   => $newId,
                    ':title' => $form['title'],
                    ':json'  => $form['fields_json'],
                ]
            );
        }

        return $newId;
    }
}
