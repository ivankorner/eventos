<?php
/**
 * Validador de datos de formulario
 * Uso:
 *   $v = new Validator($_POST);
 *   $v->required('nombre')->minLength('nombre', 3)->email('correo');
 *   if ($v->fails()) { $errors = $v->errors(); }
 */

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // -------------------------
    // Reglas de validación
    // -------------------------

    public function required(string|array $fields, string $label = ''): static
    {
        foreach ((array) $fields as $field) {
            $value = $this->data[$field] ?? '';
            if ($value === '' || $value === null) {
                $name = $label ?: $this->humanize($field);
                $this->errors[$field][] = "El campo «{$name}» es obligatorio.";
            }
        }
        return $this;
    }

    public function email(string $field, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» debe ser un email válido.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && mb_strlen($value) < $min) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» debe tener al menos {$min} caracteres.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && mb_strlen($value) > $max) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» no puede superar los {$max} caracteres.";
        }
        return $this;
    }

    public function numeric(string $field, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !is_numeric($value)) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» debe ser un número.";
        }
        return $this;
    }

    public function min(string $field, float $min, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && is_numeric($value) && (float)$value < $min) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» debe ser mayor o igual a {$min}.";
        }
        return $this;
    }

    public function max(string $field, float $max, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && is_numeric($value) && (float)$value > $max) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» debe ser menor o igual a {$max}.";
        }
        return $this;
    }

    public function url(string $field, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_URL)) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» debe ser una URL válida.";
        }
        return $this;
    }

    public function date(string $field, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '') {
            $d = \DateTime::createFromFormat('Y-m-d', $value);
            if (!$d || $d->format('Y-m-d') !== $value) {
                $name = $label ?: $this->humanize($field);
                $this->errors[$field][] = "El campo «{$name}» debe ser una fecha válida (AAAA-MM-DD).";
            }
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label = ''): static
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El valor del campo «{$name}» no es válido.";
        }
        return $this;
    }

    public function confirmed(string $field, string $label = ''): static
    {
        $value        = $this->data[$field] ?? '';
        $confirmation = $this->data[$field . '_confirmation'] ?? '';
        if ($value !== $confirmation) {
            $name = $label ?: $this->humanize($field);
            $this->errors[$field][] = "El campo «{$name}» y su confirmación no coinciden.";
        }
        return $this;
    }

    /**
     * Agrega un error manual
     */
    public function addError(string $field, string $message): static
    {
        $this->errors[$field][] = $message;
        return $this;
    }

    // -------------------------
    // Resultados
    // -------------------------

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Primer error de un campo específico
     */
    public function firstError(string $field): string
    {
        return $this->errors[$field][0] ?? '';
    }

    /**
     * Convierte un nombre de campo snake_case a etiqueta legible
     */
    private function humanize(string $field): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $field));
    }
}
