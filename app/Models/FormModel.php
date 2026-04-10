<?php
/**
 * Modelo de formularios dinámicos
 */

class FormModel extends BaseModel
{
    protected string $table = 'forms';

    /**
     * Formulario activo de un evento
     */
    public function findByEvent(int $eventId): ?array
    {
        $form = $this->queryOne(
            "SELECT * FROM forms WHERE event_id = :eid AND is_active = 1 ORDER BY id DESC LIMIT 1",
            [':eid' => $eventId]
        );

        if ($form) {
            $form['fields_json_decoded'] = json_decode($form['fields_json'], true);
        }

        return $form ?: null;
    }

    /**
     * Guarda o actualiza el formulario de un evento
     * Si ya existe uno, lo actualiza; si no, lo crea
     */
    public function saveForEvent(int $eventId, string $title, array $fieldsJson, bool $activate = false): int
    {
        $existing = $this->queryOne(
            "SELECT id FROM forms WHERE event_id = :eid ORDER BY id DESC LIMIT 1",
            [':eid' => $eventId]
        );

        $jsonStr = json_encode($fieldsJson, JSON_UNESCAPED_UNICODE);

        if ($existing) {
            $data = ['title' => $title, 'fields_json' => $jsonStr];
            if ($activate) {
                $data['is_active'] = 1;
            }
            $this->update($existing['id'], $data);
            return $existing['id'];
        }

        return $this->insert([
            'event_id'   => $eventId,
            'title'      => $title,
            'fields_json' => $jsonStr,
            'is_active'  => $activate ? 1 : 0,
        ]);
    }

    /**
     * Retorna los campos del formulario como array PHP
     * Filtrado por tipo si se indica
     */
    public function getFields(int $formId, array $excludeTypes = []): array
    {
        $form = $this->find($formId);
        if (!$form) {
            return [];
        }

        $decoded = json_decode($form['fields_json'], true);
        $fields  = $decoded['fields'] ?? [];

        if (!empty($excludeTypes)) {
            $fields = array_filter($fields, fn($f) => !in_array($f['type'], $excludeTypes, true));
        }

        // Ordenar por el campo 'order'
        usort($fields, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

        return array_values($fields);
    }

    /**
     * Retorna la configuración global del formulario (settings del JSON)
     */
    public function getSettings(int $formId): array
    {
        $form = $this->find($formId);
        if (!$form) {
            return [];
        }
        $decoded = json_decode($form['fields_json'], true);
        return $decoded['settings'] ?? [];
    }
}
