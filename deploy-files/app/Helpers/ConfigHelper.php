<?php
/**
 * Helper para obtener configuración del sistema
 */

class ConfigHelper
{
    private static ?array $settings = null;

    /**
     * Obtiene el nombre de la aplicación desde la BD
     */
    public static function getAppName(): string
    {
        $settings = self::getSettings();
        return $settings['app_name'] ?? APP_NAME;
    }

    /**
     * Obtiene todas las configuraciones del sistema
     */
    private static function getSettings(): array
    {
        if (self::$settings === null) {
            try {
                $db = Database::getInstance();
                $stmt = $db->query("SELECT key_name, value_data FROM settings");
                $rows = $stmt->fetchAll();
                self::$settings = [];
                foreach ($rows as $row) {
                    self::$settings[$row['key_name']] = $row['value_data'];
                }
            } catch (\Throwable) {
                self::$settings = [];
            }
        }
        return self::$settings;
    }
}
