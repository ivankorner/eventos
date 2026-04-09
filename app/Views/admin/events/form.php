<?php
$isEdit = !empty($event);
$old    = $old ?? [];
$errors = $errors ?? [];
$success = $success ?? null;

function fieldError(array $errors, string $field): string {
    if (!empty($errors[$field])) {
        return '<p class="text-red-500 text-xs mt-1">' . htmlspecialchars($errors[$field][0], ENT_QUOTES, 'UTF-8') . '</p>';
    }
    return '';
}

function fieldVal(array $old, array|null $event, string $field, mixed $default = ''): string {
    return htmlspecialchars($old[$field] ?? $event[$field] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<div class="mt-2 max-w-4xl">
    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <form action="<?= APP_URL ?>/admin/eventos/<?= $isEdit ? $event['id'] . '/editar' : 'crear' ?>" method="POST" enctype="multipart/form-data"
          class="space-y-6">
        <?= $csrf ?>

        <!-- Datos básicos -->
        <div class="bg-white rounded-xl shadow-sm p-6 space-y-4">
            <h3 class="font-semibold text-gray-700 border-b pb-3">Información del evento</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Título <span class="text-red-500">*</span></label>
                    <input type="text" name="title" required maxlength="255"
                           value="<?= fieldVal($old, $event, 'title') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light <?= !empty($errors['title']) ? 'border-red-400' : '' ?>"
                           placeholder="Nombre del evento">
                    <?= fieldError($errors, 'title') ?>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-color-light">
                        <span class="px-3 py-2 bg-gray-50 text-gray-400 text-sm border-r border-gray-300">/evento/</span>
                        <input type="text" name="slug"
                               value="<?= fieldVal($old, $event, 'slug') ?>"
                               class="flex-1 px-3 py-2 text-sm outline-none"
                               placeholder="se-genera-automaticamente">
                    </div>
                    <p class="text-xs text-gray-400 mt-1">Si lo dejás vacío, se genera desde el título.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                        <option value="draft"     <?= (($old['status'] ?? $event['status'] ?? '') === 'draft')     ? 'selected' : '' ?>>Borrador</option>
                        <option value="published" <?= (($old['status'] ?? $event['status'] ?? '') === 'published') ? 'selected' : '' ?>>Publicado</option>
                        <option value="finished"  <?= (($old['status'] ?? $event['status'] ?? '') === 'finished')  ? 'selected' : '' ?>>Finalizado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y hora de inicio</label>
                    <input type="datetime-local" name="start_date"
                           value="<?= fieldVal($old, $event, 'start_date') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha y hora de fin</label>
                    <input type="datetime-local" name="end_date"
                           value="<?= fieldVal($old, $event, 'end_date') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación / lugar</label>
                    <input type="text" name="location"
                           value="<?= fieldVal($old, $event, 'location') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light"
                           placeholder="Av. Corrientes 1234, Buenos Aires">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cupo máximo</label>
                    <input type="number" name="max_capacity" min="1"
                           value="<?= fieldVal($old, $event, 'max_capacity') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light"
                           placeholder="Dejar vacío para sin límite">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email de notificación</label>
                    <input type="email" name="notify_email"
                           value="<?= fieldVal($old, $event, 'notify_email') ?>"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light <?= !empty($errors['notify_email']) ? 'border-red-400' : '' ?>"
                           placeholder="organizador@evento.com">
                    <?= fieldError($errors, 'notify_email') ?>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="description" rows="5"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light"
                              placeholder="Descripción del evento (admite HTML básico)"><?= htmlspecialchars($old['description'] ?? $event['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta descripción (SEO)</label>
                    <textarea name="meta_description" rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light"
                              placeholder="Descripción para Google y redes sociales (160 caracteres aprox)"><?= htmlspecialchars($old['meta_description'] ?? $event['meta_description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Imagen de portada</label>
                    <?php if ($isEdit && !empty($event['cover_image'])): ?>
                    <div class="mb-3">
                        <img src="<?= APP_URL ?>/<?= htmlspecialchars($event['cover_image'], ENT_QUOTES, 'UTF-8') ?>"
                             alt="Portada actual" class="h-32 object-cover rounded-lg border border-gray-200">
                        <p class="text-xs text-gray-400 mt-1">Imagen actual. Subí una nueva para reemplazarla.</p>
                    </div>
                    <?php endif; ?>
                    <input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-color-lighter file:text-color-secondary hover:file:bg-color-lighter">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG o WebP. Máximo 20 MB.</p>
                    <?= fieldError($errors, 'cover_image') ?>
                </div>
            </div>
        </div>

        <!-- Botones -->
        <div class="flex items-center justify-between">
            <a href="<?= APP_URL ?>/admin/eventos" class="text-sm text-gray-500 hover:text-gray-700">← Volver al listado</a>
            <div class="flex gap-3">
                <?php if ($isEdit): ?>
                <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/formulario"
                   class="px-4 py-2 border border-color-light text-color-secondary rounded-lg text-sm font-medium hover:bg-color-lighter transition-colors">
                    Constructor de formulario
                </a>
                <?php endif; ?>
                <button type="submit"
                        class="px-5 py-2 bg-color-secondary hover:bg-color-secondary text-white rounded-lg text-sm font-medium transition-colors">
                    <?= $isEdit ? 'Guardar cambios' : 'Crear evento' ?>
                </button>
            </div>
        </div>
    </form>

    <?php if ($isEdit): ?>
    <!-- Acciones rápidas de exportación -->
    <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Exportar inscripciones</h3>
        <div class="flex flex-wrap gap-3">
            <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/exportar/excel"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm transition-colors">
                Excel (.xlsx)
            </a>
            <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/exportar/csv"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm transition-colors">
                CSV
            </a>
            <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/exportar/pdf"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm transition-colors">
                PDF resumen
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>
