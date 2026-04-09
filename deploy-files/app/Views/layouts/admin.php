<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }

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

        /* Apply to existing indigo color scheme */
        .bg-color-primary { background-color: #044BD9 !important; }
        .bg-color-secondary { background-color: #1168D9 !important; }
        .text-color-secondary { color: #1168D9 !important; }
        .text-color-light { color: #6DA7F2 !important; }
        .text-color-lighter { color: #6DA7F2 !important; }
        .border-color-secondary { border-color: #1168D9 !important; }
        .border-color-light { border-color: #6DA7F2 !important; }
        .hover\:bg-color-secondary:hover { background-color: #1168D9 !important; }
        .hover\:text-color-lighter:hover { color: #6DA7F2 !important; }

        .sidebar-active { background-color: #1168D9 !important; color: white; }

        /* Alert colors */
        .bg-green-50 { background-color: #f0fdf4 !important; }
        .border-green-200 { border-color: #bbf7d0 !important; }
        .text-green-800 { color: #166534 !important; }
        .text-green-600 { color: #16a34a !important; }

        .bg-red-50 { background-color: #fef2f2 !important; }
        .border-red-200 { border-color: #fecaca !important; }
        .text-red-800 { color: #7f1d1d !important; }
        .text-red-600 { color: #dc2626 !important; }

        .bg-blue-50 { background-color: #eff6ff !important; }
        .border-blue-200 { border-color: #bfdbfe !important; }
        .text-blue-800 { color: #1e3a8a !important; }
        .text-blue-600 { color: #2563eb !important; }

        .bg-amber-50 { background-color: #fffbeb !important; }
        .border-amber-200 { border-color: #fde68a !important; }
        .text-amber-800 { color: #92400e !important; }
        .bg-amber-100 { background-color: #fef3c7 !important; }
        .text-amber-700 { color: #b45309 !important; }

        .bg-yellow-100 { background-color: #fef3c7 !important; }
        .text-yellow-800 { color: #713f12 !important; }

        .bg-indigo-50 { background-color: #eef2ff !important; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ sidebarOpen: false }">

<!-- Sidebar -->
<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-color-primary text-white transform transition-transform duration-200 ease-in-out"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

    <!-- Logo / nombre -->
    <div class="flex items-center justify-between px-6 py-5 border-b border-color-secondary">
        <a href="<?= APP_URL ?>/admin/dashboard" class="text-xl font-bold text-white truncate">
            <?= htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8') ?>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden text-color-light hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <!-- Usuario logueado -->
    <?php $sUser = Session::user(); ?>
    <div class="px-6 py-4 border-b border-color-secondary">
        <p class="text-sm font-medium text-white truncate"><?= htmlspecialchars($sUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
        <p class="text-xs text-color-light">
            <?php $roleLabels = ['super_admin' => 'Super Admin', 'admin' => 'Administrador', 'editor' => 'Editor']; ?>
            <?= $roleLabels[$sUser['role']] ?? $sUser['role'] ?>
        </p>
    </div>

    <!-- Navegación -->
    <?php
    $currentUri = '/' . ltrim($_GET['url'] ?? '', '/');
    $navItems = [
        ['url' => '/admin/dashboard',    'label' => 'Dashboard',      'icon' => 'home'],
        ['url' => '/admin/eventos',      'label' => 'Eventos',         'icon' => 'calendar'],
        ['url' => '/admin/inscripciones','label' => 'Inscripciones',   'icon' => 'list'],
        ['url' => '/admin/configuracion','label' => 'Configuración',   'icon' => 'cog', 'roles' => ['super_admin']],
        ['url' => '/admin/usuarios',     'label' => 'Usuarios',        'icon' => 'users', 'roles' => ['super_admin']],
    ];

    $icons = [
        'home'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'calendar' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
        'list'     => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>',
        'cog'      => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        'users'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
    ];
    ?>
    <nav class="px-3 py-4 flex-1">
        <?php foreach ($navItems as $item):
            if (!empty($item['roles']) && !in_array($sUser['role'], $item['roles'], true)) continue;
            $isActive = str_starts_with($currentUri, $item['url']);
            $activeClass = $isActive ? 'bg-color-secondary text-white' : 'text-color-lighter hover:bg-color-secondary hover:text-white';
        ?>
        <a href="<?= APP_URL . $item['url'] ?>"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg mb-1 text-sm font-medium transition-colors <?= $activeClass ?>">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <?= $icons[$item['icon']] ?? '' ?>
            </svg>
            <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Cerrar sesión -->
    <div class="px-3 py-4 border-t border-color-secondary">
        <a href="<?= APP_URL ?>/admin/logout"
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-color-lighter hover:bg-color-secondary hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Cerrar sesión
        </a>
        <a href="<?= APP_URL ?>/" target="_blank"
           class="flex items-center gap-3 px-3 py-2 mt-1 rounded-lg text-xs text-color-light hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
            </svg>
            Ver sitio público
        </a>
    </div>
</aside>

<!-- Overlay móvil -->
<div class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden"
     x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"></div>

<!-- Contenido principal -->
<div class="lg:ml-64 min-h-screen flex flex-col">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-30">
        <div class="flex items-center justify-between px-4 sm:px-6 py-3">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-gray-800">
                    <?= htmlspecialchars($pageTitle ?? '', ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="hidden sm:block"><?= date('d/m/Y') ?></span>
            </div>
        </div>
    </header>

    <!-- SweetAlert Flash Notifications -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($flashSuccess = Session::getFlash('success')): ?>
        Swal.fire({
            icon: 'success',
            title: '¡Éxito!',
            text: <?= json_encode($flashSuccess) ?>,
            confirmButtonColor: '#1168D9',
            confirmButtonText: 'Aceptar'
        });
        <?php endif; ?>

        <?php if ($flashError = Session::getFlash('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: <?= json_encode($flashError) ?>,
            confirmButtonColor: '#1168D9',
            confirmButtonText: 'Aceptar'
        });
        <?php endif; ?>

        <?php if ($flashInfo = Session::getFlash('info')): ?>
        Swal.fire({
            icon: 'info',
            title: 'Información',
            text: <?= json_encode($flashInfo) ?>,
            confirmButtonColor: '#1168D9',
            confirmButtonText: 'Aceptar'
        });
        <?php endif; ?>
    });
    </script>

    <!-- Contenido de la vista -->
    <main class="flex-1 px-4 sm:px-6 pb-8">
        <?php if (isset($content) && file_exists($content)): ?>
            <?php include $content; ?>
        <?php endif; ?>
    </main>

    <footer class="text-center text-xs text-gray-400 py-4 border-t">
        <?= htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8') ?> &copy; <?= date('Y') ?>
    </footer>
</div>

</body>
</html>
