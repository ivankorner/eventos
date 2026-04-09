<?php
$statusLabels = ['pending' => 'Pendiente', 'confirmed' => 'Confirmada', 'cancelled' => 'Cancelada'];
$statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'confirmed' => 'bg-green-100 text-green-800', 'cancelled' => 'bg-red-100 text-red-700'];
// Mostrar máximo 6 campos en la tabla
$tableFields = array_slice($formFields, 0, 6);
?>
<div class="mt-2">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="font-semibold text-gray-700"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-sm text-gray-400">
                <?= $paginator->totalItems() ?> inscripción<?= $paginator->totalItems() !== 1 ? 'es' : '' ?>
                <?php if ($event['max_capacity']): ?>
                    de <?= (int)$event['max_capacity'] ?> disponibles
                <?php endif; ?>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/exportar/excel"
               class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs transition-colors">Excel</a>
            <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/exportar/csv"
               class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs transition-colors">CSV</a>
            <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/exportar/pdf"
               class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs transition-colors">PDF</a>
        </div>
    </div>

    <!-- Filtros -->
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            <option value="">Todos los estados</option>
            <?php foreach ($statusLabels as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
            <?php endforeach; ?>
        </select>
        <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom, ENT_QUOTES, 'UTF-8') ?>"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
        <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo, ENT_QUOTES, 'UTF-8') ?>"
               class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition-colors">Filtrar</button>
        <?php if ($status || $dateFrom || $dateTo): ?>
        <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/inscripciones" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($submissions)): ?>
        <div class="p-12 text-center text-gray-400">
            <p>No hay inscripciones para mostrar.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">#</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Fecha</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Estado</th>
                    <?php foreach ($tableFields as $field): ?>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell truncate max-w-[120px]">
                        <?= htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </th>
                    <?php endforeach; ?>
                    <th class="text-right px-4 py-3 font-semibold text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($submissions as $sub): ?>
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-500"><?= (int)$sub['id'] ?></td>
                <td class="px-4 py-3 text-gray-600 hidden sm:table-cell whitespace-nowrap">
                    <?= date('d/m/Y H:i', strtotime($sub['submitted_at'])) ?>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?= $statusColors[$sub['status']] ?? 'bg-gray-100' ?>">
                        <?= $statusLabels[$sub['status']] ?? $sub['status'] ?>
                    </span>
                </td>
                <?php foreach ($tableFields as $field):
                    $val = $sub['response_data'][$field['id']] ?? '—';
                    if (is_array($val)) $val = implode(', ', $val);
                    $val = (string)$val;
                ?>
                <td class="px-4 py-3 text-gray-700 hidden md:table-cell max-w-[150px] truncate"
                    title="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(mb_strimwidth($val, 0, 40, '…'), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <?php endforeach; ?>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                    <a href="<?= APP_URL ?>/admin/inscripciones/<?= $sub['id'] ?>"
                       class="text-xs text-color-secondary hover:underline mr-2">Ver</a>
                    <a href="<?= APP_URL ?>/admin/inscripciones/<?= $sub['id'] ?>/exportar/pdf"
                       class="text-xs text-gray-500 hover:underline mr-2">PDF</a>
                    <form action="<?= APP_URL ?>/admin/inscripciones/<?= $sub['id'] ?>/eliminar" method="POST" class="inline delete-submission-form">
                        <?= Csrf::field() ?>
                        <button type="submit" class="text-xs text-red-500 hover:underline">Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <?= $paginator->render() ?>

    <div class="mt-4">
        <a href="<?= APP_URL ?>/admin/eventos/<?= $event['id'] ?>/editar" class="text-sm text-gray-500 hover:text-gray-700">← Volver al evento</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const deleteSubmissionForms = document.querySelectorAll('.delete-submission-form');

    deleteSubmissionForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Eliminar esta inscripción?',
                text: 'No se puede deshacer esta acción.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
