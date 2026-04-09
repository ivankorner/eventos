<div class="mt-2">
    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Buscar acción, usuario..."
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
        <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm">Buscar</button>
    </form>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600 w-36">Fecha</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Acción</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Usuario</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">Recurso</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($logs as $log): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 text-gray-500 text-xs whitespace-nowrap">
                    <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                </td>
                <td class="px-4 py-3">
                    <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-700">
                        <?= htmlspecialchars($log['action'], ENT_QUOTES, 'UTF-8') ?>
                    </code>
                </td>
                <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                    <?= htmlspecialchars($log['user_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($log['user_email']): ?>
                    <span class="text-gray-400 text-xs block"><?= htmlspecialchars($log['user_email'], ENT_QUOTES, 'UTF-8') ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-gray-500 hidden lg:table-cell text-xs">
                    <?= htmlspecialchars(($log['resource'] ?? '') . ($log['resource_id'] ? ' #' . $log['resource_id'] : ''), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td class="px-4 py-3 text-gray-400 font-mono text-xs hidden sm:table-cell">
                    <?= htmlspecialchars($log['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?= $paginator->render() ?>

    <div class="mt-4">
        <a href="<?= APP_URL ?>/admin/configuracion" class="text-sm text-gray-500 hover:text-gray-700">← Volver a configuración</a>
    </div>
</div>
