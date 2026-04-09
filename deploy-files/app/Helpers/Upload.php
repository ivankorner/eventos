<?php
/**
 * Helper para manejo seguro de uploads de archivos
 * Valida MIME real con finfo, genera nombres UUID únicos
 */

class Upload
{
    /**
     * Procesa un archivo subido y lo mueve al destino
     *
     * @param array  $file       Elemento de $_FILES
     * @param string $directory  Subdirectorio dentro de /public/uploads (ej: 'events')
     * @param array  $allowedMimes MIME types permitidos
     * @param int    $maxSize    Tamaño máximo en bytes
     * @return array ['path' => ruta_relativa, 'original_name' => ..., 'mime_type' => ..., 'file_size' => ...]
     * @throws RuntimeException Si el archivo no es válido
     */
    public static function process(
        array $file,
        string $directory = 'uploads',
        array $allowedMimes = [],
        int $maxSize = 0
    ): array {
        // Verificar que no hubo error en la subida
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \RuntimeException(self::uploadErrorMessage($file['error']));
        }

        // Verificar tamaño
        $maxSize = $maxSize ?: UPLOAD_MAX_SIZE;
        if ($file['size'] > $maxSize) {
            $maxMB = round($maxSize / 1024 / 1024, 1);
            throw new \RuntimeException("El archivo supera el tamaño máximo permitido ({$maxMB} MB).");
        }

        // Verificar MIME real con finfo (no confiar en el MIME del cliente)
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);

        if (!empty($allowedMimes) && !in_array($realMime, $allowedMimes, true)) {
            throw new \RuntimeException(
                "Tipo de archivo no permitido. Tipos aceptados: " . implode(', ', $allowedMimes)
            );
        }

        // Determinar extensión desde el MIME real
        $extension = self::mimeToExtension($realMime) ?? pathinfo($file['name'], PATHINFO_EXTENSION);
        $extension = strtolower($extension);

        // Generar nombre único con uniqid + sufijo aleatorio
        $uniqueName = uniqid('', true) . '_' . bin2hex(random_bytes(4)) . '.' . $extension;

        // Construir ruta de destino
        $destDir = UPLOAD_PATH . '/' . trim($directory, '/');
        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        $destPath = $destDir . '/' . $uniqueName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new \RuntimeException('No se pudo mover el archivo al destino. Verificá los permisos del servidor.');
        }

        // Ruta relativa para guardar en BD (relativa a /public)
        $relativePath = 'uploads/' . trim($directory, '/') . '/' . $uniqueName;

        return [
            'path'          => $relativePath,
            'original_name' => basename($file['name']),
            'mime_type'     => $realMime,
            'file_size'     => $file['size'],
        ];
    }

    /**
     * Elimina un archivo subido
     */
    public static function delete(string $relativePath): bool
    {
        $fullPath = PUBLIC_PATH . '/' . ltrim($relativePath, '/');
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }

    /**
     * MIME types permitidos para imágenes de portada de eventos
     */
    public static function imageMimes(): array
    {
        return ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    }

    /**
     * MIME types permitidos para adjuntos de formularios
     */
    public static function formAttachmentMimes(): array
    {
        $types = explode(',', UPLOAD_ALLOWED_TYPES);
        $mimes = [];
        foreach ($types as $ext) {
            $ext = trim($ext);
            $mimes = array_merge($mimes, self::extensionToMimes($ext));
        }
        return array_unique($mimes);
    }

    /**
     * Mapeo de extensión a MIME types
     */
    private static function extensionToMimes(string $ext): array
    {
        return match(strtolower($ext)) {
            'pdf'  => ['application/pdf'],
            'jpg', 'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'webp' => ['image/webp'],
            'gif'  => ['image/gif'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            default => [],
        };
    }

    /**
     * Mapeo de MIME type a extensión de archivo
     */
    private static function mimeToExtension(string $mime): ?string
    {
        return match($mime) {
            'image/jpeg'       => 'jpg',
            'image/png'        => 'png',
            'image/webp'       => 'webp',
            'image/gif'        => 'gif',
            'application/pdf'  => 'pdf',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            default => null,
        };
    }

    /**
     * Mensaje de error legible según el código de error de PHP
     */
    private static function uploadErrorMessage(int $code): string
    {
        return match($code) {
            UPLOAD_ERR_INI_SIZE   => 'El archivo supera el tamaño máximo configurado en el servidor (upload_max_filesize).',
            UPLOAD_ERR_FORM_SIZE  => 'El archivo supera el tamaño máximo indicado en el formulario.',
            UPLOAD_ERR_PARTIAL    => 'El archivo se subió parcialmente. Intentá de nuevo.',
            UPLOAD_ERR_NO_FILE    => 'No se seleccionó ningún archivo.',
            UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal del servidor.',
            UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
            UPLOAD_ERR_EXTENSION  => 'Una extensión de PHP detuvo la subida del archivo.',
            default               => 'Error desconocido al subir el archivo.',
        };
    }
}
