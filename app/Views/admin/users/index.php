<?php
$roleLabels = ['super_admin' => 'Super Admin', 'admin' => 'Administrador', 'editor' => 'Editor'];
$currentUser = Session::user();
?>
<div class="mt-2">
    <div class="flex justify-end mb-4">
        <a href="<?= APP_URL ?>/admin/usuarios/crear"
           class="inline-flex items-center gap-2 bg-color-secondary hover:bg-color-secondary text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Crear usuario
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Usuario</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden sm:table-cell">Rol</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Último login</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">Estado</th>
                    <th class="text-right px-5 py-3 font-semibold text-gray-600">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php foreach ($users as $u): ?>
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="font-medium text-gray-800"><?= htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="text-xs text-gray-400"><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php if ($u['must_change_password']): ?>
                    <span class="inline-flex mt-1 px-1.5 py-0.5 rounded text-xs bg-amber-100 text-amber-700">Debe cambiar pass</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 hidden sm:table-cell">
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-color-lighter text-color-secondary">
                        <?= $roleLabels[$u['role']] ?? $u['role'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-500 hidden md:table-cell text-xs">
                    <?= $u['last_login_at'] ? date('d/m/Y H:i', strtotime($u['last_login_at'])) : 'Nunca' ?>
                </td>
                <td class="px-4 py-3">
                    <?php if ($u['is_active']): ?>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs bg-green-100 text-green-700">Activo</span>
                    <?php else: ?>
                    <span class="inline-flex px-2 py-0.5 rounded text-xs bg-red-100 text-red-700">Inactivo</span>
                    <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-right">
                    <?php if ($u['id'] !== $currentUser['id']): ?>
                    <div class="flex gap-3 justify-end">
                        <form action="<?= APP_URL ?>/admin/usuarios/<?= $u['id'] ?>/activar" method="POST" class="inline">
                            <?= Csrf::field() ?>
                            <button type="submit"
                                    class="text-xs <?= $u['is_active'] ? 'text-red-500' : 'text-green-600' ?> hover:underline">
                                <?= $u['is_active'] ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </form>
                        <form action="<?= APP_URL ?>/admin/usuarios/<?= $u['id'] ?>/eliminar" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de que querés eliminar este usuario? Esta acción no se puede deshacer.');">
                            <?= Csrf::field() ?>
                            <button type="submit" class="text-xs text-red-600 hover:underline">
                                Eliminar
                            </button>
                        </form>
                    </div>
                    <?php else: ?>
                    <span class="text-xs text-gray-300">Vos mismo</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?= $paginator->render() ?>
</div>
