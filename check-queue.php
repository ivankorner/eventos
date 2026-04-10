<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/app.php';
require __DIR__ . '/config/database.php';

$db = Database::getInstance();
$stmt = $db->query('SELECT id, to_email, subject, status, attempts, created_at FROM mail_queue ORDER BY created_at DESC LIMIT 20');
$mails = $stmt->fetchAll();

echo "Total en cola: " . count($mails) . "\n";
echo str_pad("ID", 5) . " | " . str_pad("Email", 30) . " | " . str_pad("Status", 10) . " | Intentos | Fecha\n";
echo str_repeat("-", 100) . "\n";

foreach ($mails as $m) {
    echo str_pad($m['id'], 5) . " | " . str_pad(substr($m['to_email'], 0, 28), 30) . " | " . str_pad($m['status'], 10) . " | " . str_pad($m['attempts'], 9) . " | " . $m['created_at'] . "\n";
}
?>
