<?php
$statusLabels = ['published' => 'Publicado', 'draft' => 'Borrador', 'finished' => 'Finalizado'];
$statusColors = ['published' => 'bg-green-100 text-green-800', 'draft' => 'bg-gray-100 text-gray-600', 'finished' => 'bg-red-100 text-red-700'];
?>
<div class="mt-2">
    <!-- Barra de herramientas -->
    <div class="flex flex-col sm:flex-row gap-3 mb-4 justify-between">
        <form method="GET" class="flex gap-2 flex-1">
            <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                   placeholder="Buscar evento..."
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                <option value="">Todos los estados</option>
                <?php foreach ($statusLabels as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= $status === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm transition-colors">Filtrar</button>
        </form>
        <a href="<?= APP_URL ?>/admin/eventos/crear"
           class="inline-flex items-center gap-2 bg-color-secondary hover:bg-color-secondary text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Crear evento
        </a>
    </div>

    <!-- Tabla -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <?php if (empty($events)): ?>
        <div class="p-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p>No hay eventos para mostrar.</p>
            <a href="<?= APP_URL ?>/admin/eventos/crear" class="mt-3 inline-block text-color-secondary hover:underline text-sm">Crear el primer evento</a>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Título</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Estado</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden lg:table-cell">Fecha inicio</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Inscripciones</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($events as $ev): ?>
            <tr class="hover:bg-gray-50 transition-colors" x-data="{ open: false }">
                <td class="px-5 py-3">
                    <div class="font-medium text-gray-800 truncate max-w-xs">
                        <?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        /evento/<?= htmlspecialchars($ev['slug'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <!-- Estado visible en mobile -->
                    <div class="mt-1 md:hidden">
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?= $statusColors[$ev['status']] ?? 'bg-gray-100' ?>">
                            <?= $statusLabels[$ev['status']] ?? $ev['status'] ?>
                        </span>
                    </div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium <?= $statusColors[$ev['status']] ?? 'bg-gray-100' ?>">
                        <?= $statusLabels[$ev['status']] ?? $ev['status'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-600 hidden lg:table-cell whitespace-nowrap">
                    <?= $ev['start_date'] ? date('d/m/Y', strtotime($ev['start_date'])) : '—' ?>
                </td>
                <td class="px-4 py-3 hidden sm:table-cell">
                    <span class="font-semibold text-color-secondary"><?= (int)$ev['total_submissions'] ?></span>
                    <?php if ($ev['max_capacity']): ?>
                    <span class="text-gray-400 text-xs"> / <?= (int)$ev['max_capacity'] ?></span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-right" x-data="{ menuOpen: false }">
                    <div class="relative inline-block">
                        <button @click="menuOpen = !menuOpen" @click.outside="menuOpen = false"
                                class="inline-flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition-colors">
                            Acciones
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="menuOpen" x-cloak
                             class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-20">
                            <a href="<?= APP_URL ?>/admin/eventos/<?= $ev['id'] ?>/editar"
                               class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Editar evento</a>
                            <a href="<?= APP_URL ?>/admin/eventos/<?= $ev['id'] ?>/formulario"
                               class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Constructor de formulario</a>
                            <a href="<?= APP_URL ?>/admin/eventos/<?= $ev['id'] ?>/inscripciones"
                               class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Ver inscripciones</a>
                            <a href="<?= APP_URL ?>/evento/<?= htmlspecialchars($ev['slug'], ENT_QUOTES, 'UTF-8') ?>" target="_blank"
                               class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Ver página pública</a>
                            <hr class="my-1 border-gray-100">
                            <!-- Cambiar estado -->
                            <form action="<?= APP_URL ?>/admin/eventos/<?= $ev['id'] ?>/estado" method="POST">
                                <?= Csrf::field() ?>
                                <?php if ($ev['status'] !== 'published'): ?>
                                <button type="submit" name="status" value="published"
                                        class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-green-700 hover:bg-gray-50">Publicar</button>
                                <?php endif; ?>
                                <?php if ($ev['status'] !== 'draft'): ?>
                                <button type="submit" name="status" value="draft"
                                        class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Pasar a borrador</button>
                                <?php endif; ?>
                                <?php if ($ev['status'] !== 'finished'): ?>
                                <button type="submit" name="status" value="finished"
                                        class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50">Marcar finalizado</button>
                                <?php endif; ?>
                            </form>
                            <!-- Duplicar -->
                            <form action="<?= APP_URL ?>/admin/eventos/<?= $ev['id'] ?>/duplicar" method="POST">
                                <?= Csrf::field() ?>
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-color-secondary hover:bg-gray-50">Duplicar evento</button>
                            </form>
                            <hr class="my-1 border-gray-100">
                            <!-- Eliminar -->
                            <form action="<?= APP_URL ?>/admin/eventos/<?= $ev['id'] ?>/eliminar" method="POST"
                                  onsubmit="return confirm('¿Seguro que querés eliminar este evento? No se puede deshacer.')">
                                <?= Csrf::field() ?>
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50">Eliminar evento</button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

    <?= $paginator->render() ?>
</div>
