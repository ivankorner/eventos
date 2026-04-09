<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripciones — <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #1f2937; }
        .header { background: #4f46e5; color: white; padding: 16px 20px; margin-bottom: 16px; }
        .header h1 { font-size: 16px; }
        .header p { font-size: 9px; opacity: 0.8; margin-top: 4px; }
        .summary { padding: 0 20px; margin-bottom: 16px; font-size: 10px; }
        .summary p { margin-bottom: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 0 20px; width: calc(100% - 40px); }
        th { background: #4f46e5; color: white; text-align: left; padding: 6px 8px; font-size: 9px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; vertical-align: top; font-size: 9px; }
        tr:nth-child(even) td { background: #f9fafb; }
    </style>
</head>
<body>

<div class="header">
    <h1>Inscripciones: <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Generado el <?= date('d/m/Y H:i') ?> &bull; <?= count($submissions) ?> inscripciones</p>
</div>

<div class="summary">
    <?php if ($event['start_date']): ?>
    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($event['start_date'])) ?> hs</p>
    <?php endif; ?>
    <?php if ($event['location']): ?>
    <p><strong>Lugar:</strong> <?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($event['max_capacity']): ?>
    <p><strong>Cupo:</strong> <?= count($submissions) ?> / <?= $event['max_capacity'] ?></p>
    <?php endif; ?>
</div>

<?php
$dataFields = array_filter($formFields, fn($f) => !in_array($f['type'], ['heading', 'paragraph']));
$dataFields = array_values($dataFields);
// Mostrar máximo 6 campos en el PDF masivo para que no sea ilegible
$dataFields = array_slice($dataFields, 0, 6);
?>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Fecha</th>
            <th>Estado</th>
            <?php foreach ($dataFields as $f): ?>
            <th><?= htmlspecialchars($f['label'] ?? '', ENT_QUOTES, 'UTF-8') ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
    <?php
    $statusMap = ['pending'=>'Pendiente','confirmed'=>'Confirmada','cancelled'=>'Cancelada'];
    foreach ($submissions as $i => $sub):
    ?>
    <tr>
        <td><?= $i + 1 ?></td>
        <td><?= date('d/m/Y', strtotime($sub['submitted_at'])) ?></td>
        <td><?= $statusMap[$sub['status']] ?? $sub['status'] ?></td>
        <?php foreach ($dataFields as $f):
            $val = $sub['response_data'][$f['id']] ?? '—';
            if (is_array($val)) $val = implode(', ', $val);
        ?>
        <td><?= htmlspecialchars(mb_strimwidth((string)$val, 0, 50, '…'), ENT_QUOTES, 'UTF-8') ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
