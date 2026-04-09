<div class="mt-2 max-w-3xl space-y-6">

    <!-- General -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-700 border-b pb-3 mb-5">Configuración general</h3>
        <form action="<?= APP_URL ?>/admin/configuracion" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= $csrf ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del sistema</label>
                <input type="text" name="app_name"
                       value="<?= htmlspecialchars($settings['app_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título hero (página pública)</label>
                <input type="text" name="hero_title"
                       value="<?= htmlspecialchars($settings['hero_title'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtítulo hero</label>
                <input type="text" name="hero_subtitle"
                       value="<?= htmlspecialchars($settings['hero_subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Frase en el footer</label>
                <input type="text" name="footer_text"
                       value="<?= htmlspecialchars($settings['footer_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       placeholder="Ej: Todos los derechos reservados © 2026"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Logo del sistema</label>
                <?php if (!empty($settings['app_logo'])): ?>
                <div class="mb-2">
                    <img src="<?= APP_URL ?>/<?= htmlspecialchars($settings['app_logo'], ENT_QUOTES, 'UTF-8') ?>"
                         alt="Logo" class="h-12 object-contain border rounded p-1">
                </div>
                <?php endif; ?>
                <input type="file" name="app_logo" accept="image/*"
                       class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:bg-color-lighter file:text-color-secondary">
            </div>
            <button type="submit" class="px-5 py-2 bg-color-secondary hover:bg-color-secondary text-white rounded-lg text-sm font-medium transition-colors">
                Guardar configuración
            </button>
        </form>
    </div>

    <!-- Email de prueba -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-700 border-b pb-3 mb-5">Verificar configuración SMTP</h3>
        <p class="text-sm text-gray-500 mb-4">
            La configuración SMTP se toma del archivo <code class="bg-gray-100 px-1 rounded">.env</code>.
            Enviá un email de prueba para verificar que funciona correctamente.
        </p>
        <form action="<?= APP_URL ?>/admin/configuracion/email-prueba" method="POST" class="flex gap-3">
            <?= $csrf ?>
            <input type="email" name="test_email" required placeholder="tu@email.com"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
            <button type="submit" class="px-4 py-2 bg-color-primary hover:bg-color-secondary text-white rounded-lg text-sm transition-colors">
                Enviar prueba
            </button>
        </form>
    </div>

    <!-- Log de auditoría (link) -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="font-semibold text-gray-700 border-b pb-3 mb-4">Log de auditoría</h3>
        <p class="text-sm text-gray-500 mb-3">
            Registro de todas las acciones importantes realizadas en el sistema.
        </p>
        <a href="<?= APP_URL ?>/admin/configuracion/audit-log"
           class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition-colors">
            Ver log de auditoría →
        </a>
    </div>

</div>
