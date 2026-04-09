<?php
/**
 * Singleton de conexión PDO a la base de datos
 * Uso: $db = Database::getInstance();
 */

class Database
{
    private static ?PDO $instance = null;

    /**
     * Retorna la instancia única de PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $host    = $_ENV['DB_HOST'] ?? 'localhost';
            $port    = $_ENV['DB_PORT'] ?? '3306';
            $dbname  = $_ENV['DB_NAME'] ?? 'inscripciones_db';
            $user    = $_ENV['DB_USER'] ?? 'root';
            $pass    = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

            try {
                self::$instance = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    // Deshabilitar buffered queries para grandes resultados
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                ]);
            } catch (PDOException $e) {
                // Loguear el error real pero mostrar mensaje genérico
                error_log('DB Connection Error: ' . $e->getMessage());
                if (APP_DEBUG) {
                    throw $e;
                }
                http_response_code(500);
                die('Error de conexión a la base de datos. Contactá al administrador.');
            }
        }

        return self::$instance;
    }

    // Evitar clonación y deserialización del singleton
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new \RuntimeException('No se puede deserializar el singleton Database.');
    }
}
