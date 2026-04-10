<?php
/**
 * Script de configuración interactivo de SMTP
 * Ejecutar: /parlamentos/public/setup-email
 */

// Detectar si es CLI o web
$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Email</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 8px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 10px; }
        .info { background: #e7f3ff; color: #004085; padding: 12px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #004085; }
        .form-group { margin: 20px 0; }
        label { display: block; font-weight: 600; color: #333; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; font-size: 14px; }
        input:focus { outline: none; border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        button { background: #4f46e5; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; font-weight: 600; }
        button:hover { background: #4338ca; }
        .error { color: #dc2626; font-size: 14px; margin-top: 5px; }
        .success { background: #dcfce7; color: #166534; padding: 12px; border-radius: 4px; margin: 20px 0; border-left: 4px solid #16a34a; }
        .options { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 20px 0; }
        .option { padding: 15px; border: 2px solid #ddd; border-radius: 4px; cursor: pointer; text-align: center; }
        .option:hover { border-color: #4f46e5; background: #f9f5ff; }
        .option input[type="radio"] { display: none; }
        .option input[type="radio"]:checked + label { color: #4f46e5; font-weight: 700; }
        label { margin: 0; cursor: pointer; }
        .step { margin: 30px 0; padding: 20px; background: #f9f9f9; border-radius: 4px; }
        .step h3 { color: #4f46e5; margin-bottom: 10px; }
        code { background: #f0f0f0; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 13px; }
    </style>
</head>
<body>
<div class="container">
    <h1>⚙️ Configuración de Email</h1>
    <div class="info">ℹ️ Este script te ayudará a configurar el servidor SMTP para enviar emails de confirmación de inscripción.</div>

    <form method="POST">
        <div class="form-group">
            <label>Selecciona el proveedor de correo:</label>
            <div class="options">
                <div class="option">
                    <input type="radio" id="gmail" name="provider" value="gmail" checked>
                    <label for="gmail">📧 Gmail</label>
                </div>
                <div class="option">
                    <input type="radio" id="custom" name="provider" value="custom">
                    <label for="custom">🔧 Personalizado</label>
                </div>
            </div>
        </div>

        <div id="gmail-config" class="step">
            <h3>Configuración de Gmail</h3>
            <p>Necesitas una <strong>Contraseña de Aplicación</strong> de Google, no tu contraseña normal.</p>
            <div class="form-group">
                <label for="gmail_email">Email de Gmail:</label>
                <input type="email" id="gmail_email" name="gmail_email" placeholder="tu@gmail.com" value="' . ($_POST['gmail_email'] ?? '') . '">
            </div>
            <div class="form-group">
                <label for="gmail_password">Contraseña de Aplicación:</label>
                <input type="password" id="gmail_password" name="gmail_password" placeholder="Contraseña de 16 caracteres">
            </div>
            <button type="submit" name="test_gmail">🧪 Probar configuración</button>
        </div>

        <div id="custom-config" class="step" style="display:none;">
            <h3>Servidor SMTP Personalizado</h3>
            <div class="form-group">
                <label for="smtp_host">Host SMTP:</label>
                <input type="text" id="smtp_host" name="smtp_host" placeholder="smtp.ejemplo.com" value="' . ($_POST['smtp_host'] ?? '') . '">
            </div>
            <div class="form-group">
                <label for="smtp_port">Puerto:</label>
                <input type="number" id="smtp_port" name="smtp_port" placeholder="587" value="' . ($_POST['smtp_port'] ?? '587') . '">
            </div>
            <div class="form-group">
                <label for="smtp_user">Usuario:</label>
                <input type="text" id="smtp_user" name="smtp_user" placeholder="usuario@ejemplo.com" value="' . ($_POST['smtp_user'] ?? '') . '">
            </div>
            <div class="form-group">
                <label for="smtp_pass">Contraseña:</label>
                <input type="password" id="smtp_pass" name="smtp_pass" placeholder="Contraseña">
            </div>
            <button type="submit" name="test_custom">🧪 Probar configuración</button>
        </div>
    </form>

    <script>
        document.querySelectorAll("input[name='provider']").forEach(el => {
            el.addEventListener("change", function() {
                document.getElementById("gmail-config").style.display = this.value === "gmail" ? "block" : "none";
                document.getElementById("custom-config").style.display = this.value === "custom" ? "block" : "none";
            });
        });
    </script>
</div>
</body>
</html>';
    exit;
}

// CLI Mode
echo "⚙️  CONFIGURACIÓN DE EMAIL\n";
echo "========================\n\n";
echo "Opciones:\n";
echo "1) Gmail\n";
echo "2) Servidor personalizado (appcde.online, etc.)\n";
echo "3) Salir\n\n";
echo "Selecciona opción (1-3): ";

$input = trim(fgets(STDIN));
if ($input === '3') exit;

if ($input === '1') {
    echo "\n📧 CONFIGURACIÓN DE GMAIL\n";
    echo "========================\n\n";
    echo "Email: ";
    $email = trim(fgets(STDIN));
    echo "Contraseña de Aplicación (16 caracteres): ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";

    // Guardar en .env
    updateEnv([
        'MAIL_DRIVER' => 'smtp',
        'MAIL_HOST' => 'smtp.gmail.com',
        'MAIL_PORT' => '587',
        'MAIL_ENCRYPTION' => 'tls',
        'MAIL_USERNAME' => $email,
        'MAIL_PASSWORD' => $password,
        'MAIL_FROM_ADDRESS' => $email,
        'MAIL_FROM_NAME' => 'Sistema de Inscripciones'
    ]);

    echo "✅ Configuración guardada. Prueba con:\n";
    echo "   php /Applications/XAMPP/bin/php test-email.php\n";
} elseif ($input === '2') {
    echo "\n🔧 SERVIDOR PERSONALIZADO\n";
    echo "========================\n\n";
    echo "Host: ";
    $host = trim(fgets(STDIN));
    echo "Puerto (587): ";
    $port = trim(fgets(STDIN)) ?: '587';
    echo "Usuario: ";
    $user = trim(fgets(STDIN));
    echo "Contraseña: ";
    system('stty -echo');
    $pass = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";

    updateEnv([
        'MAIL_DRIVER' => 'smtp',
        'MAIL_HOST' => $host,
        'MAIL_PORT' => $port,
        'MAIL_ENCRYPTION' => 'tls',
        'MAIL_USERNAME' => $user,
        'MAIL_PASSWORD' => $pass,
        'MAIL_FROM_ADDRESS' => $user,
        'MAIL_FROM_NAME' => 'Sistema de Inscripciones'
    ]);

    echo "✅ Configuración guardada.\n";
}

function updateEnv($updates) {
    $envFile = __DIR__ . '/.env';
    if (!file_exists($envFile)) {
        die("❌ No se encontró .env\n");
    }

    $content = file_get_contents($envFile);

    foreach ($updates as $key => $value) {
        // Escapar valor
        $escapedValue = preg_replace('/(["\'])/', '\\\\$1', $value);

        // Buscar y reemplazar o agregar
        if (preg_match("/^{$key}=/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
        } else {
            $content .= "\n{$key}={$escapedValue}";
        }
    }

    file_put_contents($envFile, $content);
}
