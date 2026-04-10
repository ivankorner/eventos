<?php
/**
 * Diagnóstico de email v2 — SOLO USO TEMPORAL, ELIMINAR DESPUÉS DE USAR
 * URL: https://appcde.online/eventos/public/diagnostico-email.php?key=diagmail2026&to=TU@EMAIL.COM
 */

if (($_GET['key'] ?? '') !== 'diagmail2026') {
    http_response_code(403);
    die('Acceso denegado.');
}

// Bootstrap completo de la app
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/config/app.php';

// Cargar clases necesarias
spl_autoload_register(function ($class) {
    $paths = [
        BASE_PATH . '/app/Helpers/' . $class . '.php',
        BASE_PATH . '/app/Models/'  . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) { require_once $path; break; }
    }
});

require_once BASE_PATH . '/app/Helpers/Email.php';

$destino = trim($_GET['to'] ?? '');
$modo    = $_GET['modo'] ?? 'info'; // info | test

?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Diagnóstico Email v2</title>
<style>
  body  { font-family: monospace; background: #111; color: #eee; padding: 24px; max-width: 860px; margin: 0 auto; }
  .ok   { color: #4ade80; font-weight: bold; }
  .fail { color: #f87171; font-weight: bold; }
  .warn { color: #fbbf24; }
  .info { color: #60a5fa; }
  pre   { background: #1e1e1e; padding: 12px; border-radius: 6px; font-size: 11px; overflow-x: auto; white-space: pre-wrap; }
  h2    { color: #a78bfa; border-bottom: 1px solid #333; padding-bottom: 4px; margin-top: 32px; }
  table { border-collapse: collapse; width: 100%; margin: 8px 0; }
  td,th { border: 1px solid #333; padding: 6px 12px; }
  th    { background: #1e1e1e; }
  .box  { background: #1a1a2e; border: 1px solid #4338ca; border-radius: 8px; padding: 16px; margin: 16px 0; }
  a     { color: #818cf8; }
  input { background: #222; color: #eee; border: 1px solid #444; padding: 6px 10px; border-radius: 4px; width: 280px; }
  button { background: #4338ca; color: #fff; border: none; padding: 8px 20px; border-radius: 4px; cursor: pointer; }
</style>
</head>
<body>
<h1>&#128272; Diagnóstico de Email v2</h1>
<p class="warn">&#9888; Eliminar del servidor después de usar.</p>

<?php if (!$destino): ?>
<div class="box">
  <p class="warn">&#9888; <strong>Ingresá tu email para recibir el test:</strong></p>
  <form method="get">
    <input type="hidden" name="key" value="diagmail2026">
    <input type="hidden" name="modo" value="test">
    <input type="email" name="to" placeholder="tu@email.com" required>
    &nbsp;<button type="submit">Enviar test &#8594;</button>
  </form>
</div>
<?php endif; ?>

<h2>1. Variables de entorno (.env)</h2>
<?php
$vars = ['APP_ENV','APP_URL','MAIL_HOST','MAIL_PORT','MAIL_ENCRYPTION','MAIL_USERNAME','MAIL_PASSWORD','MAIL_FROM_ADDRESS'];
echo '<table><tr><th>Variable</th><th>Valor</th><th>Estado</th></tr>';
foreach ($vars as $v) {
    $val = $_ENV[$v] ?? null;
    if ($v === 'MAIL_PASSWORD' && $val) {
        $display = str_repeat('*', max(0, strlen($val)-3)) . substr($val, -3) . ' (long: ' . strlen($val) . ')';
    } else {
        $display = $val ?? '—';
    }
    $ok = $val ? "<span class='ok'>&#10003; OK</span>" : "<span class='fail'>&#10007; No definida</span>";
    echo "<tr><td>{$v}</td><td>" . htmlspecialchars($display) . "</td><td>{$ok}</td></tr>";
}
echo '</table>';
?>

<h2>2. Carga de config/mail.php</h2>
<?php
try {
    $mailConfig = require BASE_PATH . '/config/mail.php';
    echo '<table><tr><th>Clave</th><th>Valor</th></tr>';
    foreach ($mailConfig as $k => $v) {
        $display = $k === 'password' ? str_repeat('*', max(0, strlen($v)-3)) . substr($v, -3) : $v;
        echo "<tr><td>{$k}</td><td>" . htmlspecialchars((string)$display) . "</td></tr>";
    }
    echo '</table>';
    echo "<p class='ok'>&#10003; config/mail.php cargado correctamente</p>";
} catch (\Throwable $e) {
    echo "<p class='fail'>&#10007; Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

<h2>3. Prueba de envío con Email::send() de la app</h2>
<?php if (!$destino): ?>
<p class="warn">Ingresá tu email arriba para ejecutar esta prueba.</p>
<?php else: ?>
<p>Enviando a: <strong><?= htmlspecialchars($destino) ?></strong></p>
<?php
try {
    $html = '
    <!DOCTYPE html><html><body style="font-family:Arial;background:#f3f4f6;padding:20px">
    <div style="max-width:500px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden">
      <div style="background:#4338ca;padding:20px;text-align:center">
        <h2 style="color:#fff;margin:0">Test de Email — ' . APP_NAME . '</h2>
      </div>
      <div style="padding:24px">
        <p>&#10003; <strong>El email llegó correctamente.</strong></p>
        <p>Servidor SMTP: <code>' . htmlspecialchars($_ENV['MAIL_HOST'] ?? '?') . '</code></p>
        <p>Hora del test: <code>' . date('d/m/Y H:i:s') . '</code></p>
        <p>Si estás viendo esto, los emails de inscripción funcionan.</p>
      </div>
    </div>
    </body></html>';

    Email::send($destino, 'Test', '[TEST] Diagnóstico email — ' . date('H:i:s'), $html);
    echo "<p class='ok'>&#10003; Email enviado exitosamente a <strong>" . htmlspecialchars($destino) . "</strong></p>";
    echo "<p>Revisá tu bandeja de entrada <strong>y la carpeta de SPAM</strong>.</p>";
} catch (\Throwable $e) {
    echo "<p class='fail'>&#10007; Error al enviar: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
<?php endif; ?>

<hr style="border-color:#333;margin-top:40px">
<p class="warn" style="font-size:12px">&#9888; Eliminar: <code>public/diagnostico-email.php</code></p>
</body>
</html>
