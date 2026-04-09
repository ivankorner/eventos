<?php
/**
 * Modelo de log de auditoría
 */

class AuditLogModel extends BaseModel
{
    protected string $table = 'audit_logs';

    /**
     * Registra una acción en el log de auditoría
     *
     * @param string   $action      Identificador de la acción (ej: 'event.created')
     * @param string   $resource    Nombre del recurso (ej: 'Event')
     * @param int|null $resourceId  ID del recurso afectado
     * @param array    $details     Datos adicionales (se guarda como JSON)
     */
    public static function log(string $action, string $resource = '', ?int $resourceId = null, array $details = []): void
    {
        try {
            $db     = Database::getInstance();
            $userId = Session::user()['id'] ?? null;
            $ip     = $_SERVER['REMOTE_ADDR'] ?? null;

            $db->prepare(
                "INSERT INTO audit_logs (user_id, action, resource, resource_id, details, ip_address)
                 VALUES (:user_id, :action, :resource, :resource_id, :details, :ip)"
            )->execute([
                ':user_id'     => $userId,
                ':action'      => $action,
                ':resource'    => $resource,
                ':resource_id' => $resourceId,
                ':details'     => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                ':ip'          => $ip,
            ]);
        } catch (\Throwable $e) {
            // El log de auditoría nunca debe romper el flujo de la aplicación
            ErrorHandler::log('AuditLog error: ' . $e->getMessage());
        }
    }

    /**
     * Retorna el log paginado para el panel de configuración
     */
    public function getPaginated(int $limit, int $offset, string $search = ''): array
    {
        $where  = [];
        $binds  = [];

        if ($search !== '') {
            $where[]          = '(al.action LIKE :search OR u.name LIKE :search OR al.resource LIKE :search)';
            $binds[':search'] = '%' . $search . '%';
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT al.*, u.name AS user_name, u.email AS user_email
             FROM audit_logs al
             LEFT JOIN users u ON u.id = al.user_id
             {$whereStr}
             ORDER BY al.created_at DESC
             LIMIT :limit OFFSET :offset"
        );

        foreach ($binds as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function countAll(string $search = ''): int
    {
        if ($search !== '') {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) FROM audit_logs al LEFT JOIN users u ON u.id = al.user_id
                 WHERE al.action LIKE :search OR u.name LIKE :search"
            );
            $stmt->execute([':search' => '%' . $search . '%']);
        } else {
            $stmt = $this->db->query("SELECT COUNT(*) FROM audit_logs");
        }
        return (int) $stmt->fetchColumn();
    }
}
