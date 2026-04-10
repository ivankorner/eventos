<?php
/**
 * Página web para configurar y probar email
 * Accede a: /parlamentos/public/setup-email-web
 */

// Cargar .env
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    http_response_code(500);
    die('❌ Archivo .env no encontrado');
}

// Parsear .env
$env = [];
$lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
foreach ($lines as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (strpos($line, '=') === false) continue;

    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);
    if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
        (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
        $value = substr($value, 1, -1);
    }
    $env[$key] = $value;
}

$status = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update') {
        $updates = [
            'MAIL_HOST' => $_POST['MAIL_HOST'] ?? '',
            'MAIL_PORT' => $_POST['MAIL_PORT'] ?? '',
            'MAIL_USERNAME' => $_POST['MAIL_USERNAME'] ?? '',
            'MAIL_PASSWORD' => $_POST['MAIL_PASSWORD'] ?? '',
            'MAIL_FROM_ADDRESS' => $_POST['MAIL_FROM_ADDRESS'] ?? '',
        ];

        $content = file_get_contents($envFile);
        foreach ($updates as $key => $value) {
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            }
        }

        if (file_put_contents($envFile, $content)) {
            $status = '✅ Configuración actualizada';
            // Recargar env
            $env = array_merge($env, $updates);
        } else {
            $error = '❌ Error al guardar .env';
        }
    } elseif ($action === 'test') {
        // Recargar configuración
        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../app/Helpers/Email.php';
        require_once __DIR__ . '/../app/Helpers/ErrorHandler.php';
        require_once __DIR__ . '/../app/Helpers/ConfigHelper.php';

        try {
            $result = Email::processQueue(5);
            $status = "✅ Procesamiento completado: {$result['sent']} enviados, {$result['failed']} fallidos";
        } catch (\Throwable $e) {
            $error = '❌ Error: ' . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Email</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; padding: 30px; margin: 20px 0; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        h1 { color: white; margin-bottom: 20px; text-align: center; font-size: 28px; }
        h2 { color: #667eea; margin: 20px 0 15px 0; font-size: 18px; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        .status { padding: 15px; border-radius: 8px; margin: 20px 0; }
        .success { background: #dcfce7; color: #166534; border: 1px solid #16a34a; }
        .error { background: #fee2e2; color: #991b1b; border: 1px solid #dc2626; }
        .info { background: #dbeafe; color: #1e40af; border: 1px solid #3b82f6; }
        .form-group { margin: 15px 0; }
        label { display: block; color: #333; font-weight: 600; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .preset-buttons { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; margin: 15px 0; }
        button { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5568d3; }
        .btn-secondary { background: #e5e7eb; color: #333; }
        .btn-secondary:hover { background: #d1d5db; }
        .config-display { background: #f3f4f6; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; line-height: 1.6; }
        .preset { padding: 10px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; }
        .preset:hover { background: #f0f0f0; border-color: #667eea; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; }
        .icon { font-size: 20px; margin-right: 5px; }
    </style>
</head>
<body>
<div class="container">
    <h1><span class="icon">⚙️</span> Configuración de Email</h1>

    <div class="card">
        <?php if ($status): ?>
            <div class="status success"><?= htmlspecialchars($status) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="status error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <h2>Estado Actual</h2>
        <div class="config-display">
            Host: <strong><?= htmlspecialchars($env['MAIL_HOST'] ?? 'No configurado') ?></strong><br>
            Port: <strong><?= htmlspecialchars($env['MAIL_PORT'] ?? '587') ?></strong><br>
            Username: <strong><?= htmlspecialchars($env['MAIL_USERNAME'] ?? 'No configurado') ?></strong><br>
            From: <strong><?= htmlspecialchars($env['MAIL_FROM_ADDRESS'] ?? 'No configurado') ?></strong>
        </div>

        <h2>Presets Recomendados</h2>
        <div class="preset-buttons">
            <button class="preset btn-secondary" onclick="setPreset('gmail')">📧 Gmail</button>
            <button class="preset btn-secondary" onclick="setPreset('mailtrap')">🧪 Mailtrap</button>
            <button class="preset btn-secondary" onclick="setPreset('appcde')">🔧 appcde.online</button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="update">

            <h2>Configuración Manual</h2>
            <div class="form-group">
                <label for="MAIL_HOST">Host SMTP:</label>
                <input type="text" id="MAIL_HOST" name="MAIL_HOST" value="<?= htmlspecialchars($env['MAIL_HOST'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="MAIL_PORT">Puerto:</label>
                <input type="number" id="MAIL_PORT" name="MAIL_PORT" value="<?= htmlspecialchars($env['MAIL_PORT'] ?? 587) ?>">
            </div>

            <div class="form-group">
                <label for="MAIL_USERNAME">Usuario:</label>
                <input type="text" id="MAIL_USERNAME" name="MAIL_USERNAME" value="<?= htmlspecialchars($env['MAIL_USERNAME'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="MAIL_PASSWORD">Contraseña:</label>
                <input type="password" id="MAIL_PASSWORD" name="MAIL_PASSWORD" placeholder="●●●●●●●●">
            </div>

            <div class="form-group">
                <label for="MAIL_FROM_ADDRESS">Desde (Email):</label>
                <input type="email" id="MAIL_FROM_ADDRESS" name="MAIL_FROM_ADDRESS" value="<?= htmlspecialchars($env['MAIL_FROM_ADDRESS'] ?? '') ?>">
            </div>

            <button type="submit" class="btn-primary"><span class="icon">💾</span> Guardar Configuración</button>
        </form>

        <form method="POST" style="margin-top: 20px;">
            <input type="hidden" name="action" value="test">
            <button type="submit" class="btn-secondary"><span class="icon">🧪</span> Procesar Cola de Emails</button>
        </form>

        <h2 style="margin-top: 30px;">Guías Rápidas</h2>
        <div class="info" style="margin: 15px 0; padding: 15px;">
            <strong>📧 Gmail:</strong> Necesitas una <code>contraseña de aplicación</code> (no tu contraseña normal)
            <a href="https://support.google.com/accounts/answer/185833" target="_blank">→ Cómo generar</a>
        </div>
        <div class="info" style="margin: 15px 0; padding: 15px;">
            <strong>🧪 Mailtrap:</strong> SMTP de prueba gratuito. Sin credenciales reales, solo para desarrollo.
            <a href="https://mailtrap.io" target="_blank">→ Crear cuenta</a>
        </div>
        <div class="info" style="margin: 15px 0; padding: 15px;">
            <strong>🔧 appcde.online:</strong> Verifica en el panel Don Web las credenciales del servidor de correo.
        </div>
    </div>
</div>

<script>
function setPreset(type) {
    const presets = {
        gmail: {
            host: 'smtp.gmail.com',
            port: 587,
            user: 'tu_email@gmail.com',
            password: 'tu_app_password'
        },
        mailtrap: {
            host: 'sandbox.smtp.mailtrap.io',
            port: 2525,
            user: '2c844e9ec0e60a',
            password: '6a6f25e7fccc4f'
        },
        appcde: {
            host: 'appcde.online',
            port: 587,
            user: 'no-reply@appcde.online',
            password: 'A26_H3{/N]z<'
        }
    };

    const preset = presets[type];
    if (preset) {
        document.getElementById('MAIL_HOST').value = preset.host;
        document.getElementById('MAIL_PORT').value = preset.port;
        document.getElementById('MAIL_USERNAME').value = preset.user;
        document.getElementById('MAIL_PASSWORD').value = preset.password;
    }
}
</script>
</body>
</html>
