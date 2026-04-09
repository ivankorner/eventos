<?php
$statusLabels = ['pending' => 'Pendiente', 'confirmed' => 'Confirmada', 'cancelled' => 'Cancelada'];
$statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'confirmed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-700'];
?>
<div class="mt-2 max-w-3xl">
    <?php if ($success): ?>
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php endif; ?>

    <!-- Header de la inscripción -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Inscripción #<?= (int)$submission['id'] ?></h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Evento: <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/editar" class="text-color-secondary hover:underline">
                        <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium <?= $statusColors[$submission['status']] ?? 'bg-gray-100' ?>">
                    <?= $statusLabels[$submission['status']] ?? $submission['status'] ?>
                </span>
                <a href="<?= APP_URL ?>/admin/inscripciones/<?= $submission['id'] ?>/exportar/pdf"
                   class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs transition-colors">
                    Descargar PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-5 pt-5 border-t border-gray-100 text-sm">
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">Fecha de envío</p>
                <p class="text-gray-700 mt-1"><?= date('d/m/Y H:i:s', strtotime($submission['submitted_at'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase tracking-wide">IP de origen</p>
                <p class="text-gray-700 mt-1 font-mono"><?= htmlspecialchars($submission['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>

    <!-- Respuestas del formulario -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-5">
        <h3 class="font-semibold text-gray-700 mb-4">Respuestas del formulario</h3>
        <dl class="space-y-4">
            <?php foreach ($formFields as $field):
                if (in_array($field['type'], ['heading', 'paragraph'], true)) continue;
                $val = $submission['response_data'][$field['id']] ?? null;
                $label = htmlspecialchars($field['label'] ?? 'Campo', ENT_QUOTES, 'UTF-8');
            ?>
            <div class="grid grid-cols-3 gap-3 py-3 border-b border-gray-50 last:border-0">
                <dt class="text-sm font-medium text-gray-500 col-span-1"><?= $label ?></dt>
                <dd class="text-sm text-gray-800 col-span-2">
                    <?php if ($val === null || $val === ''): ?>
                    <span class="text-gray-300 italic">Sin respuesta</span>
                    <?php elseif (is_array($val)): ?>
                    <?= htmlspecialchars(implode(', ', $val), ENT_QUOTES, 'UTF-8') ?>
                    <?php elseif ($field['type'] === 'file'): ?>
                    <a href="<?= APP_URL ?>/<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" class="text-color-secondary hover:underline">Ver archivo adjunto</a>
                    <?php else: ?>
                    <?= nl2br(htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8')) ?>
                    <?php endif; ?>
                </dd>
            </div>
            <?php endforeach; ?>
        </dl>
    </div>

    <!-- Cambiar estado -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-700 mb-4">Actualizar estado</h3>
        <form action="<?= APP_URL ?>/admin/inscripciones/<?= $submission['id'] ?>/estado" method="POST" class="flex items-center gap-3">
            <?= $csrf ?>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                <?php foreach ($statusLabels as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $submission['status'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="px-4 py-2 bg-color-secondary hover:bg-color-secondary text-white rounded-lg text-sm transition-colors">
                Actualizar
            </button>
        </form>
    </div>

    <div class="mt-4">
        <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/inscripciones"
           class="text-sm text-gray-500 hover:text-gray-700">← Volver a inscripciones</a>
    </div>
</div>
