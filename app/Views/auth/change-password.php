<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña — <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Whitebird Bank Color Palette */
        .color-primary { color: #044BD9; }
        .bg-color-primary { background-color: #044BD9; }

        .color-secondary { color: #1168D9; }
        .bg-color-secondary { background-color: #1168D9; }

        .color-light { color: #4B94F2; }
        .bg-color-light { background-color: #4B94F2; }

        .color-lighter { color: #6DA7F2; }
        .bg-color-lighter { background-color: #6DA7F2; }

        .color-dark { color: #0D0D0D; }
        .bg-color-dark { background-color: #0D0D0D; }

        /* Focus ring color */
        .focus\:ring-color-lighter:focus { --tw-ring-color: #6DA7F2; }

        /* Alert colors */
        .bg-amber-50 { background-color: #fffbeb !important; }
        .border-amber-200 { border-color: #fde68a !important; }
        .text-amber-800 { color: #92400e !important; }

        .bg-red-50 { background-color: #fef2f2 !important; }
        .border-red-200 { border-color: #fecaca !important; }
        .text-red-700 { color: #b91c1c !important; }
    </style>
</head>
<body class="bg-color-primary min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-2">Cambiar contraseña</h2>

        <?php if (!empty($forced)): ?>
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg px-4 py-3 mb-5 text-sm">
            Debés cambiar tu contraseña antes de continuar.
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/admin/cambiar-password" method="POST">
            <?= $csrf ?>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
                <input type="password" name="current_password" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-lighter">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                <input type="password" name="new_password" required minlength="8"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-lighter">
                <p class="text-xs text-gray-400 mt-1">Mínimo 8 caracteres.</p>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                <input type="password" name="new_password_confirmation" required minlength="8"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-lighter">
            </div>

            <button type="submit"
                    class="w-full bg-color-secondary hover:bg-color-secondary text-white font-semibold py-2.5 rounded-lg text-sm transition-colors">
                Guardar nueva contraseña
            </button>
        </form>
    </div>
</div>
</body>
</html>
