<div class="min-h-screen flex items-center justify-center px-4 py-16">
    <div class="text-center max-w-md">
        <!-- Ícono de éxito -->
        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-3">¡Inscripción recibida!</h1>

        <?php if ($eventTitle): ?>
        <p class="text-gray-500 mb-3">
            Tu inscripción a <strong><?= htmlspecialchars($eventTitle, ENT_QUOTES, 'UTF-8') ?></strong> fue procesada correctamente.
        </p>
        <?php endif; ?>

        <p class="text-gray-600 mb-8 leading-relaxed">
            <?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?>
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <?php if ($slug): ?>
            <a href="<?= APP_URL ?>/evento/<?= htmlspecialchars($slug, ENT_QUOTES, 'UTF-8') ?>"
               class="px-5 py-2.5 border border-color-light text-color-secondary rounded-xl text-sm font-medium hover:bg-color-lighter transition-colors">
                ← Volver al evento
            </a>
            <?php endif; ?>
            <a href="<?= APP_URL ?>/"
               class="px-5 py-2.5 bg-color-secondary hover:bg-color-secondary text-white rounded-xl text-sm font-medium transition-colors">
                Ver todos los eventos
            </a>
        </div>
    </div>
</div>
