<?php
/**
 * Modelo de usuarios del sistema
 */

class UserModel extends BaseModel
{
    protected string $table = 'users';

    /**
     * Busca un usuario por email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->queryOne(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            [':email' => $email]
        );
    }

    /**
     * Verifica si el usuario está bloqueado por demasiados intentos fallidos
     */
    public function isLocked(string $email, string $ip, int $maxAttempts = 5, int $lockMinutes = 15): bool
    {
        $since = date('Y-m-d H:i:s', strtotime("-{$lockMinutes} minutes"));
        $stmt  = $this->db->prepare(
            "SELECT COUNT(*) FROM login_attempts
             WHERE email = :email AND ip_address = :ip AND attempted_at >= :since"
        );
        $stmt->execute([':email' => $email, ':ip' => $ip, ':since' => $since]);
        return (int) $stmt->fetchColumn() >= $maxAttempts;
    }

    /**
     * Registra un intento de login fallido
     */
    public function recordFailedAttempt(string $email, string $ip): void
    {
        $this->db->prepare(
            "INSERT INTO login_attempts (email, ip_address) VALUES (:email, :ip)"
        )->execute([':email' => $email, ':ip' => $ip]);
    }

    /**
     * Limpia los intentos fallidos de un email (tras login exitoso)
     */
    public function clearLoginAttempts(string $email): void
    {
        $this->db->prepare(
            "DELETE FROM login_attempts WHERE email = :email"
        )->execute([':email' => $email]);
    }

    /**
     * Registra la sesión activa del usuario
     */
    public function createSession(int $userId, string $token, string $ip, string $userAgent): void
    {
        $expires = date('Y-m-d H:i:s', time() + SESSION_LIFETIME);
        $this->db->prepare(
            "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at)
             VALUES (:uid, :token, :ip, :ua, :expires)"
        )->execute([
            ':uid'     => $userId,
            ':token'   => $token,
            ':ip'      => $ip,
            ':ua'      => $userAgent,
            ':expires' => $expires,
        ]);
    }

    /**
     * Actualiza el timestamp de último login
     */
    public function updateLastLogin(int $userId): void
    {
        $this->db->prepare(
            "UPDATE users SET last_login_at = NOW() WHERE id = :id"
        )->execute([':id' => $userId]);
    }

    /**
     * Retorna el historial de sesiones de un usuario (para el perfil)
     */
    public function getLoginHistory(int $userId, int $limit = 10): array
    {
        return $this->query(
            "SELECT ip_address, user_agent, created_at FROM user_sessions
             WHERE user_id = :uid ORDER BY created_at DESC LIMIT :limit",
            [':uid' => $userId]
        );
    }

    /**
     * Genera una contraseña temporal segura de 12 caracteres
     */
    public static function generateTempPassword(): string
    {
        $chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789!@#$%&*';
        $len   = 12;
        $pass  = '';
        for ($i = 0; $i < $len; $i++) {
            $pass .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $pass;
    }

    /**
     * Retorna todos los usuarios para el panel admin (con paginación)
     */
    public function getAllPaginated(int $limit, int $offset): array
    {
        return $this->query(
            "SELECT id, name, email, role, is_active, must_change_password, last_login_at, created_at
             FROM users ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
            []
        );
    }

    /**
     * Soft-disable de usuario (no borra el registro)
     */
    public function toggleActive(int $id): bool
    {
        return (bool) $this->execute(
            "UPDATE users SET is_active = NOT is_active WHERE id = :id",
            [':id' => $id]
        );
    }

    /**
     * Elimina un usuario del sistema
     */
    public function delete(int $id): bool
    {
        return (bool) $this->execute(
            "DELETE FROM users WHERE id = :id",
            [':id' => $id]
        );
    }
}
