<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>404 — Página no encontrada</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .color-light { color: #4B94F2; }
    .bg-color-secondary { background-color: #1168D9; }
    .hover\:bg-color-secondary:hover { background-color: #1168D9 !important; }
</style></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="text-center">
    <h1 class="text-8xl font-bold text-color-light">404</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mt-4">Página no encontrada</h2>
    <p class="text-gray-500 mt-2">La página que buscás no existe o fue eliminada.</p>
    <a href="<?= APP_URL ?>/" class="mt-6 inline-block bg-color-secondary text-white px-6 py-2.5 rounded-lg hover:bg-color-secondary transition-colors">Volver al inicio</a>
</div>
</body></html>
