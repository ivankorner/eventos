<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Error interno</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
    .bg-color-secondary { background-color: #1168D9; }
    .hover\:bg-color-secondary:hover { background-color: #1168D9 !important; }
    .text-red-300 { color: #fca5a5 !important; }
</style></head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
<div class="text-center">
    <h1 class="text-8xl font-bold text-red-300">500</h1>
    <h2 class="text-2xl font-semibold text-gray-700 mt-4">Error interno del servidor</h2>
    <p class="text-gray-500 mt-2">Ocurrió un error inesperado. El equipo fue notificado.</p>
    <a href="<?= APP_URL ?>/" class="mt-6 inline-block bg-color-secondary text-white px-6 py-2.5 rounded-lg hover:bg-color-secondary transition-colors">Volver al inicio</a>
</div>
</body></html>
