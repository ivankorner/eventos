<?php
/**
 * Rate limiting basado en base de datos
 * Compatible con shared hosting sin Redis/Memcached
 */

class RateLimit
{
    /**
     * Verifica si la IP supera el límite para una acción dada
     *
     * @param string $action Nombre de la acción (ej: 'form_submit', 'login')
     * @param string $ip     IP del cliente
     * @param int    $limit  Máximo de intentos en la ventana
     * @param int    $window Ventana de tiempo en segundos
     * @return bool True si está dentro del límite, False si lo superó
     */
    public static function check(string $action, string $ip, int $limit = 0, int $window = 0): bool
    {
        $limit  = $limit  ?: RATE_LIMIT_SUBMISSIONS;
        $window = $window ?: RATE_LIMIT_WINDOW;

        $db = Database::getInstance();

        // Limpiar registros viejos (fuera de la ventana)
        $cleanSince = date('Y-m-d H:i:s', time() - $window);
        $db->prepare("DELETE FROM `login_attempts` WHERE attempted_at < :since AND email = :action")
            ->execute([':since' => $cleanSince, ':action' => "rl:{$action}:{$ip}"]);

        // Contar intentos dentro de la ventana
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM `login_attempts`
             WHERE email = :action AND ip_address = :ip AND attempted_at >= :since"
        );
        $stmt->execute([
            ':action' => "rl:{$action}:{$ip}",
            ':ip'     => $ip,
            ':since'  => $cleanSince,
        ]);

        $count = (int) $stmt->fetchColumn();

        return $count < $limit;
    }

    /**
     * Registra un intento (llamar después de check())
     */
    public static function hit(string $action, string $ip): void
    {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO `login_attempts` (email, ip_address) VALUES (:action, :ip)"
        )->execute([
            ':action' => "rl:{$action}:{$ip}",
            ':ip'     => $ip,
        ]);
    }

    /**
     * Obtiene la IP real del cliente (considera proxies)
     */
    public static function getClientIp(): string
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback: cualquier IP del REMOTE_ADDR aunque sea privada
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}
