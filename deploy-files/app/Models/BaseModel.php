<?php
/**
 * Modelo base con métodos PDO comunes
 * Todas las queries usan prepared statements — prohibido concatenar variables de usuario
 */

abstract class BaseModel
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------
    // Operaciones CRUD básicas
    // -------------------------

    /**
     * Busca un registro por su clave primaria
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Retorna todos los registros de la tabla (sin soft-delete)
     */
    public function findAll(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query(
            "SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}"
        );
        return $stmt->fetchAll();
    }

    /**
     * Inserta un registro y retorna el ID generado
     */
    public function insert(array $data): int
    {
        $columns = implode('`, `', array_keys($data));
        $params  = implode(', ', array_map(fn($k) => ':' . $k, array_keys($data)));
        $binds   = [];
        foreach ($data as $key => $value) {
            $binds[':' . $key] = $value;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO `{$this->table}` (`{$columns}`) VALUES ({$params})"
        );
        $stmt->execute($binds);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Actualiza un registro por ID
     */
    public function update(int $id, array $data): bool
    {
        $sets  = implode(', ', array_map(fn($k) => "`{$k}` = :{$k}", array_keys($data)));
        $binds = [':id' => $id];
        foreach ($data as $key => $value) {
            $binds[':' . $key] = $value;
        }

        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = :id"
        );

        return $stmt->execute($binds);
    }

    /**
     * Soft delete: marca deleted_at en lugar de borrar físicamente
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE `{$this->table}` SET `deleted_at` = NOW() WHERE `{$this->primaryKey}` = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Borrado físico (usar con cuidado)
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id"
        );
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Cuenta registros (opcionalmente con condición simple)
     */
    public function count(string $where = '', array $binds = []): int
    {
        $sql  = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Paginación genérica
     */
    public function paginate(int $limit, int $offset, string $where = '', array $binds = [], string $orderBy = 'id DESC'): array
    {
        $sql = "SELECT * FROM `{$this->table}`";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        $sql .= " ORDER BY {$orderBy} LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($binds as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Ejecuta una query personalizada con prepared statement
     * Solo para casos donde los métodos genéricos no alcancen
     */
    protected function query(string $sql, array $binds = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        return $stmt->fetchAll();
    }

    protected function queryOne(string $sql, array $binds = []): ?array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($binds);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    protected function execute(string $sql, array $binds = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($binds);
    }
}
