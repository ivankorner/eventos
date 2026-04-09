<?php
// Helpers de formato local
function formatNum(int $n): string { return number_format($n, 0, ',', '.'); }
function statusBadge(string $s): string {
    $map = ['published' => 'bg-green-100 text-green-800', 'draft' => 'bg-gray-100 text-gray-600', 'finished' => 'bg-red-100 text-red-700'];
    $labels = ['published' => 'Publicado', 'draft' => 'Borrador', 'finished' => 'Finalizado'];
    $cls = $map[$s] ?? 'bg-gray-100 text-gray-600';
    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ' . $cls . '">' . ($labels[$s] ?? $s) . '</span>';
}
?>
<!-- Tarjetas de métricas -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-2 mb-6">
    <?php
    $cards = [
        ['label' => 'Eventos activos',      'value' => $metrics['active_events'],   'color' => 'primary', 'icon' => 'calendar'],
        ['label' => 'Total de eventos',     'value' => $metrics['total_events'],    'color' => 'secondary', 'icon' => 'list'],
        ['label' => 'Inscripciones totales','value' => $metrics['total_submissions'],'color' => 'light',   'icon' => 'user-group'],
        ['label' => 'Inscripciones (7 días)','value' => $metrics['week_submissions'],'color' => 'lighter','icon' => 'trending-up'],
    ];
    $iconPaths = [
        'calendar'    => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'list'        => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'user-group'  => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'trending-up' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
    ];
    $colorMap = ['primary' => '#044BD9', 'secondary' => '#1168D9', 'light' => '#4B94F2', 'lighter' => '#6DA7F2'];
    foreach ($cards as $card):
    ?>
    <div class="bg-white rounded-xl shadow-sm p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: <?= $colorMap[$card['color']] ?>22;">
            <svg class="w-6 h-6" fill="none" stroke="<?= $colorMap[$card['color']] ?>" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $iconPaths[$card['icon']] ?>"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-bold text-gray-800"><?= formatNum((int)$card['value']) ?></p>
            <p class="text-sm text-gray-500"><?= $card['label'] ?></p>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Alertas de eventos próximos a vencer -->
<?php if (!empty($metrics['expiring_soon'])): ?>
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
    <h3 class="font-semibold text-amber-800 mb-2 flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        Eventos próximos a vencer
    </h3>
    <ul class="space-y-1">
        <?php foreach ($metrics['expiring_soon'] as $exp): ?>
        <li class="text-sm text-amber-700">
            <a href="<?= APP_URL ?>/admin/eventos/<?= $exp['id'] ?>/editar" class="font-medium hover:underline">
                <?= htmlspecialchars($exp['title'], ENT_QUOTES, 'UTF-8') ?>
            </a>
            — vence el <?= date('d/m/Y H:i', strtotime($exp['end_date'])) ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Gráfico de inscripciones -->
    <div class="xl:col-span-2 bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Inscripciones — últimos 30 días</h3>
        <canvas id="chartInscripciones" height="120"></canvas>
    </div>

    <!-- Últimas inscripciones -->
    <div class="bg-white rounded-xl shadow-sm p-5">
        <h3 class="font-semibold text-gray-700 mb-4">Últimas inscripciones</h3>
        <?php if (empty($recentSubs)): ?>
        <p class="text-sm text-gray-400">No hay inscripciones todavía.</p>
        <?php else: ?>
        <ul class="space-y-3">
            <?php foreach ($recentSubs as $sub): ?>
            <li class="flex items-start gap-2 text-sm border-b border-gray-50 pb-3 last:border-0 last:pb-0">
                <div class="w-2 h-2 rounded-full bg-color-light mt-1.5 flex-shrink-0"></div>
                <div class="min-w-0">
                    <a href="<?= APP_URL ?>/admin/eventos/<?= $sub['event_id'] ?>/inscripciones"
                       class="text-gray-700 font-medium hover:text-color-secondary truncate block">
                        <?= htmlspecialchars($sub['event_title'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <span class="text-gray-400 text-xs"><?= date('d/m H:i', strtotime($sub['submitted_at'])) ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js solo en el dashboard -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartInscripciones').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= $chartLabels ?>,
        datasets: [{
            label: 'Inscripciones',
            data: <?= $chartValues ?>,
            backgroundColor: 'rgba(99, 102, 241, 0.2)',
            borderColor: 'rgba(99, 102, 241, 1)',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } },
            x: { grid: { display: false } }
        }
    }
});
</script>
