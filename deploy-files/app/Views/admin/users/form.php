<?php $errors = $errors ?? []; ?>
<div class="mt-2 max-w-lg">
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-5">Crear nuevo usuario</h2>
        <p class="text-sm text-gray-500 mb-6">
            Se generará una contraseña temporal y se enviará al email del usuario.
            Deberá cambiarla en el primer ingreso.
        </p>

        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-5">
            <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
            <?php foreach ($errors as $fieldErrors): ?>
                <?php foreach ($fieldErrors as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/admin/usuarios/crear" method="POST">
            <?= $csrf ?>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required maxlength="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light"
                           placeholder="Juan Pérez">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light"
                           placeholder="usuario@ejemplo.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                    <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                        <option value="admin">Administrador</option>
                        <option value="editor">Editor</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">
                        Admin: crea y gestiona sus propios eventos. Editor: solo puede ver inscripciones.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="<?= APP_URL ?>/admin/usuarios" class="text-sm text-gray-500 hover:text-gray-700">← Cancelar</a>
                <button type="submit" class="px-5 py-2 bg-color-secondary hover:bg-color-secondary text-white rounded-lg text-sm font-medium transition-colors">
                    Crear usuario y enviar email
                </button>
            </div>
        </form>
    </div>
</div>
