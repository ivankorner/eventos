<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscripción #<?= (int)$submission['id'] ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
        .header { background: #4f46e5; color: white; padding: 20px 25px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; margin-bottom: 4px; }
        .header p { font-size: 10px; opacity: 0.8; }
        .section { padding: 0 25px; margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #6366f1; letter-spacing: 0.5px; margin-bottom: 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        .info-grid { display: table; width: 100%; border-collapse: collapse; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; font-weight: bold; color: #6b7280; width: 35%; padding: 5px 8px 5px 0; vertical-align: top; }
        .info-value { display: table-cell; color: #1f2937; padding: 5px 0; vertical-align: top; }
        .response-table { width: 100%; border-collapse: collapse; }
        .response-table th { background: #f3f4f6; text-align: left; padding: 7px 10px; font-size: 10px; font-weight: bold; color: #6b7280; border: 1px solid #e5e7eb; }
        .response-table td { padding: 7px 10px; border: 1px solid #e5e7eb; vertical-align: top; }
        .response-table tr:nth-child(even) td { background: #f9fafb; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 9px; font-weight: bold; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .footer { margin-top: 30px; padding: 15px 25px; border-top: 1px solid #e5e7eb; font-size: 9px; color: #9ca3af; display: flex; justify-content: space-between; }
    </style>
</head>
<body>

<div class="header">
    <h1>Comprobante de Inscripción</h1>
    <p><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?> &bull; Generado el <?= date('d/m/Y \a \l\a\s H:i') ?></p>
</div>

<div class="section">
    <div class="section-title">Datos del evento</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Evento:</div>
            <div class="info-value"><?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php if ($event['start_date']): ?>
        <div class="info-row">
            <div class="info-label">Fecha:</div>
            <div class="info-value"><?= date('d/m/Y H:i', strtotime($event['start_date'])) ?> hs</div>
        </div>
        <?php endif; ?>
        <?php if ($event['location']): ?>
        <div class="info-row">
            <div class="info-label">Lugar:</div>
            <div class="info-value"><?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="section">
    <div class="section-title">Datos de la inscripción</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">N° de inscripción:</div>
            <div class="info-value">#<?= (int)$submission['id'] ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Fecha de envío:</div>
            <div class="info-value"><?= date('d/m/Y H:i:s', strtotime($submission['submitted_at'])) ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Estado:</div>
            <div class="info-value">
                <?php $statusMap = ['pending'=>'Pendiente','confirmed'=>'Confirmada','cancelled'=>'Cancelada']; ?>
                <span class="badge badge-<?= $submission['status'] ?>">
                    <?= $statusMap[$submission['status']] ?? $submission['status'] ?>
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">IP de origen:</div>
            <div class="info-value"><?= htmlspecialchars($submission['ip_address'] ?? '—', ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Respuestas del formulario</div>
    <table class="response-table">
        <thead>
            <tr>
                <th style="width:35%">Campo</th>
                <th>Respuesta</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($formFields as $field):
            if (in_array($field['type'], ['heading', 'paragraph'], true)) continue;
            $val = $submission['response_data'][$field['id']] ?? '—';
            if (is_array($val)) $val = implode(', ', $val);
        ?>
        <tr>
            <td><strong><?= htmlspecialchars($field['label'] ?? 'Campo', ENT_QUOTES, 'UTF-8') ?></strong></td>
            <td><?= htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') ?: '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="footer">
    <span><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></span>
    <span>Inscripción #<?= (int)$submission['id'] ?></span>
</div>

</body>
</html>
