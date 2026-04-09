<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <?php if (!empty($metaDesc)): ?>
    <meta name="description" content="<?= htmlspecialchars($metaDesc, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <?php if (!empty($ogImage)): ?>
    <meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:type" content="website">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.0/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        [x-cloak]{ display:none !important; }

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

        /* Apply to existing color classes */
        .text-color-secondary { color: #1168D9 !important; }
        .text-color-light { color: #6DA7F2 !important; }
        .text-color-lighter { color: #6DA7F2 !important; }
        .border-color-secondary { border-color: #1168D9 !important; }
        .border-color-light { border-color: #6DA7F2 !important; }
        .hover\:bg-color-secondary:hover { background-color: #1168D9 !important; }
        .hover\:text-color-lighter:hover { color: #6DA7F2 !important; }

        /* Alert colors */
        .bg-green-50 { background-color: #f0fdf4 !important; }
        .border-green-200 { border-color: #bbf7d0 !important; }
        .text-green-800 { color: #166534 !important; }
        .text-green-600 { color: #16a34a !important; }

        .bg-red-50 { background-color: #fef2f2 !important; }
        .border-red-200 { border-color: #fecaca !important; }
        .text-red-800 { color: #7f1d1d !important; }
        .text-red-600 { color: #dc2626 !important; }
        .text-red-700 { color: #b91c1c !important; }

        .bg-blue-50 { background-color: #eff6ff !important; }
        .border-blue-200 { border-color: #bfdbfe !important; }
        .text-blue-800 { color: #1e3a8a !important; }
        .text-blue-600 { color: #2563eb !important; }
        .text-blue-700 { color: #1d4ed8 !important; }

        .bg-amber-50 { background-color: #fffbeb !important; }
        .border-amber-200 { border-color: #fde68a !important; }
        .text-amber-800 { color: #92400e !important; }
        .bg-amber-100 { background-color: #fef3c7 !important; }
        .text-amber-700 { color: #b45309 !important; }

        .bg-yellow-100 { background-color: #fef3c7 !important; }
        .text-yellow-800 { color: #713f12 !important; }

        .bg-indigo-50 { background-color: #eef2ff !important; }
        .text-indigo-600 { color: #4f46e5 !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">

<!-- Contenido -->
<main class="flex-1">
    <?php if (isset($content) && file_exists($content)): ?>
        <?php include $content; ?>
    <?php endif; ?>
</main>

<!-- Footer -->
<footer class="bg-color-primary text-color-lighter py-8 mt-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
        <p class="text-sm"><?= htmlspecialchars(ConfigHelper::getAppName(), ENT_QUOTES, 'UTF-8') ?> &copy; <?= date('Y') ?></p>
        <?php if (!empty($footerText)): ?>
        <p class="text-sm mt-2"><?= htmlspecialchars($footerText, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
    </div>
</footer>

</body>
</html>
