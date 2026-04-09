<?php
/**
 * Modelo de inscripciones / respuestas de formulario
 */

class SubmissionModel extends BaseModel
{
    protected string $table = 'submissions';

    /**
     * Inscripciones de un evento con paginación
     */
    public function getByEvent(int $eventId, int $limit, int $offset, string $status = '', string $search = '', string $dateFrom = '', string $dateTo = ''): array
    {
        $where  = ['s.event_id = :event_id', 's.deleted_at IS NULL'];
        $binds  = [':event_id' => $eventId];

        if ($status) {
            $where[]          = 's.status = :status';
            $binds[':status'] = $status;
        }
        if ($dateFrom) {
            $where[]             = 'DATE(s.submitted_at) >= :date_from';
            $binds[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where[]           = 'DATE(s.submitted_at) <= :date_to';
            $binds[':date_to'] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT s.* FROM submissions s
             WHERE {$whereStr}
             ORDER BY s.submitted_at DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($binds as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $submissions = $stmt->fetchAll();

        // Decodificar el JSON de respuestas en cada fila
        foreach ($submissions as &$sub) {
            if (is_string($sub['response_data'])) {
                $sub['response_data'] = json_decode($sub['response_data'], true);
            }
        }

        return $submissions;
    }

    public function countByEvent(int $eventId, string $status = '', string $dateFrom = '', string $dateTo = ''): int
    {
        $where  = ['event_id = :event_id', 'deleted_at IS NULL'];
        $binds  = [':event_id' => $eventId];

        if ($status) {
            $where[]          = 'status = :status';
            $binds[':status'] = $status;
        }
        if ($dateFrom) {
            $where[]             = 'DATE(submitted_at) >= :date_from';
            $binds[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where[]           = 'DATE(submitted_at) <= :date_to';
            $binds[':date_to'] = $dateTo;
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM submissions WHERE {$whereStr}");
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Todas las inscripciones con paginación (sin filtrar por evento)
     */
    public function getAll(int $limit, int $offset, string $status = '', string $dateFrom = '', string $dateTo = '', ?int $userId = null): array
    {
        $where  = ['s.deleted_at IS NULL'];
        $binds  = [];

        if ($status) {
            $where[]          = 's.status = :status';
            $binds[':status'] = $status;
        }
        if ($dateFrom) {
            $where[]             = 'DATE(s.submitted_at) >= :date_from';
            $binds[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where[]           = 'DATE(s.submitted_at) <= :date_to';
            $binds[':date_to'] = $dateTo;
        }
        if ($userId !== null) {
            $where[]       = 'e.user_id = :user_id';
            $binds[':user_id'] = $userId;
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $this->db->prepare(
            "SELECT s.*, e.title as event_title FROM submissions s
             LEFT JOIN events e ON s.event_id = e.id
             WHERE {$whereStr}
             ORDER BY s.submitted_at DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($binds as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $submissions = $stmt->fetchAll();

        foreach ($submissions as &$sub) {
            if (is_string($sub['response_data'])) {
                $sub['response_data'] = json_decode($sub['response_data'], true);
            }
        }

        return $submissions;
    }

    /**
     * Cantidad total de inscripciones (sin filtrar por evento)
     */
    public function countAll(string $status = '', string $dateFrom = '', string $dateTo = '', ?int $userId = null): int
    {
        $where  = ['s.deleted_at IS NULL'];
        $binds  = [];

        if ($status) {
            $where[]          = 's.status = :status';
            $binds[':status'] = $status;
        }
        if ($dateFrom) {
            $where[]             = 'DATE(s.submitted_at) >= :date_from';
            $binds[':date_from'] = $dateFrom;
        }
        if ($dateTo) {
            $where[]           = 'DATE(s.submitted_at) <= :date_to';
            $binds[':date_to'] = $dateTo;
        }
        if ($userId !== null) {
            $where[]       = 'e.user_id = :user_id';
            $binds[':user_id'] = $userId;
        }

        $whereStr = implode(' AND ', $where);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM submissions s LEFT JOIN events e ON s.event_id = e.id WHERE {$whereStr}");
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Retorna UNA inscripción con response_data decodificado
     */
    public function findWithData(int $id): ?array
    {
        $sub = $this->find($id);
        if ($sub && is_string($sub['response_data'])) {
            $sub['response_data'] = json_decode($sub['response_data'], true);
        }
        return $sub;
    }

    /**
     * Todas las inscripciones de un evento sin paginación (para exportar)
     */
    public function getAllByEvent(int $eventId): array
    {
        $subs = $this->query(
            "SELECT * FROM submissions WHERE event_id = :eid AND deleted_at IS NULL ORDER BY submitted_at ASC",
            [':eid' => $eventId]
        );

        foreach ($subs as &$sub) {
            if (is_string($sub['response_data'])) {
                $sub['response_data'] = json_decode($sub['response_data'], true);
            }
        }

        return $subs;
    }

    /**
     * Guarda una nueva inscripción y retorna el ID
     */
    public function create(int $formId, int $eventId, array $responseData, string $ip, string $userAgent): int
    {
        return $this->insert([
            'form_id'       => $formId,
            'event_id'      => $eventId,
            'response_data' => json_encode($responseData, JSON_UNESCAPED_UNICODE),
            'status'        => 'pending',
            'ip_address'    => $ip,
            'user_agent'    => substr($userAgent, 0, 500),
        ]);
    }

    /**
     * Actualiza el estado de una inscripción
     */
    public function updateStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Verifica si ya existe una inscripción del mismo email para un evento
     * (búsqueda en el JSON — no usa índice, pero es compatible con todos los DBMS)
     */
    public function emailAlreadyRegistered(int $eventId, string $fieldId, string $email): bool
    {
        // Búsqueda con JSON_EXTRACT (MySQL 5.7+ / MariaDB 10.2+)
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM submissions
                 WHERE event_id = :eid AND deleted_at IS NULL
                 AND JSON_UNQUOTE(JSON_EXTRACT(response_data, :path)) = :email"
            );
            $stmt->execute([
                ':eid'   => $eventId,
                ':path'  => '$.\"' . $fieldId . '\"',
                ':email' => $email,
            ]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException) {
            // Fallback si JSON_EXTRACT no está disponible: búsqueda por LIKE
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM submissions
                 WHERE event_id = :eid AND deleted_at IS NULL
                 AND response_data LIKE :email"
            );
            $stmt->execute([':eid' => $eventId, ':email' => '%' . $email . '%']);
            return (int) $stmt->fetchColumn() > 0;
        }
    }
}
