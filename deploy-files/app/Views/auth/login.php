<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — <?= htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .bg-red-50 { background-color: #fef2f2 !important; }
        .border-red-200 { border-color: #fecaca !important; }
        .text-red-700 { color: #b91c1c !important; }

        .bg-blue-50 { background-color: #eff6ff !important; }
        .border-blue-200 { border-color: #bfdbfe !important; }
        .text-blue-700 { color: #1d4ed8 !important; }
    </style>
</head>
<body class="bg-color-primary min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Logo / Título -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-white"><?= htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-color-light mt-1">Panel administrativo</p>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-2xl p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-6">Iniciar sesión</h2>

        <?php if (!empty($error)): ?>
        <div class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 mb-5 text-sm">
            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php endif; ?>

        <?php if (!empty($info)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-lg px-4 py-3 mb-5 text-sm">
            <?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>

        <form action="<?= APP_URL ?>/admin/login" method="POST" x-data="{ loading: false }" @submit="loading = true">
            <?= $csrf ?>

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required autocomplete="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-lighter focus:border-transparent"
                       placeholder="admin@sistema.com">
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-lighter focus:border-transparent"
                       placeholder="••••••••">
            </div>

            <button type="submit"
                    :disabled="loading"
                    class="w-full bg-color-secondary hover:bg-color-secondary disabled:opacity-60 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-colors">
                <span x-show="!loading">Ingresar</span>
                <span x-show="loading" x-cloak>Ingresando...</span>
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-color-light mt-4">
        <a href="<?= APP_URL ?>/" class="hover:text-color-lighter transition-colors">← Ver sitio público</a>
    </p>
</div>

</body>
</html>
