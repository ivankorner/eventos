<?php
/**
 * Helper para generación de slugs URL-amigables
 * Convierte "Título del Evento" → "titulo-del-evento"
 */

class Slug
{
    /**
     * Genera un slug a partir de un texto
     */
    public static function generate(string $text): string
    {
        // Transliterar caracteres especiales del español al equivalente ASCII
        $map = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
            'ñ' => 'n', 'Ñ' => 'n',
            'ç' => 'c', 'Ç' => 'c',
            'ß' => 'ss',
        ];

        $text = strtr($text, $map);

        // Convertir a minúsculas
        $text = strtolower($text);

        // Reemplazar todo lo que no sea letra, número o guión por guión
        $text = preg_replace('/[^a-z0-9\-]/', '-', $text);

        // Eliminar guiones múltiples consecutivos
        $text = preg_replace('/-+/', '-', $text);

        // Eliminar guiones al inicio y al final
        $text = trim($text, '-');

        return $text ?: 'sin-titulo';
    }

    /**
     * Genera un slug único verificando contra la base de datos
     * Si "mi-evento" ya existe, retorna "mi-evento-2", "mi-evento-3", etc.
     *
     * @param string   $text     Texto base para el slug
     * @param string   $table    Nombre de la tabla donde verificar unicidad
     * @param int|null $excludeId ID a excluir (para edición del mismo registro)
     */
    public static function unique(string $text, string $table = 'events', ?int $excludeId = null): string
    {
        $db   = Database::getInstance();
        $base = self::generate($text);
        $slug = $base;
        $i    = 2;

        while (true) {
            if ($excludeId) {
                $stmt = $db->prepare(
                    "SELECT id FROM `{$table}` WHERE slug = :slug AND id != :id LIMIT 1"
                );
                $stmt->execute([':slug' => $slug, ':id' => $excludeId]);
            } else {
                $stmt = $db->prepare(
                    "SELECT id FROM `{$table}` WHERE slug = :slug LIMIT 1"
                );
                $stmt->execute([':slug' => $slug]);
            }

            if (!$stmt->fetch()) {
                // El slug es único
                break;
            }

            // Ya existe, agregar sufijo numérico
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
