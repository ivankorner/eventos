<?php
$now = time();
function eventStatus(array $event): string {
    $now = time();
    $start = $event['start_date'] ? strtotime($event['start_date']) : null;
    $end   = $event['end_date']   ? strtotime($event['end_date'])   : null;
    if ($end && $end < $now) return 'Finalizado';
    if ($start && $start <= $now && (!$end || $end >= $now)) return 'En curso';
    return 'Próximo';
}
function eventStatusColor(string $s): string {
    return match($s) {
        'En curso'   => 'bg-green-100 text-green-800',
        'Finalizado' => 'bg-gray-100 text-gray-600',
        default      => 'bg-color-lighter text-color-secondary',
    };
}
?>
<!-- Hero section -->
<section class="bg-color-primary text-white py-20 px-4">
    <div class="max-w-4xl mx-auto text-center">
        <?php if (!empty($settings['hero_image'])): ?>
        <div class="absolute inset-0 opacity-10 bg-cover bg-center" style="background-image:url('<?= APP_URL ?>/<?= htmlspecialchars($settings['hero_image'], ENT_QUOTES, 'UTF-8') ?>')"></div>
        <?php endif; ?>
        <h1 class="text-4xl sm:text-5xl font-bold mb-4 relative">
            <?= htmlspecialchars($settings['hero_title'] ?? APP_NAME, ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="text-xl text-color-lighter relative">
            <?= htmlspecialchars($settings['hero_subtitle'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
    </div>
</section>

<!-- Filtros rápidos -->
<section class="bg-white border-b border-gray-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-2" x-data="{ filter: 'all' }">
        <span class="text-sm text-gray-500 mr-2">Filtrar:</span>
        <button @click="filter = 'all'; filterEvents('all')"
                class="px-3 py-1 rounded-full text-sm transition-colors"
                :class="filter === 'all' ? 'bg-color-secondary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            Todos
        </button>
        <button @click="filter = 'upcoming'; filterEvents('upcoming')"
                class="px-3 py-1 rounded-full text-sm transition-colors"
                :class="filter === 'upcoming' ? 'bg-color-secondary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            Próximos
        </button>
        <button @click="filter = 'ongoing'; filterEvents('ongoing')"
                class="px-3 py-1 rounded-full text-sm transition-colors"
                :class="filter === 'ongoing' ? 'bg-color-secondary text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
            En curso
        </button>
    </div>
</section>

<!-- Grilla de eventos -->
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-10">
    <?php if (empty($events)): ?>
    <div class="text-center py-16 text-gray-400">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
        <p class="text-lg">No hay eventos publicados en este momento.</p>
        <p class="mt-2 text-sm">Volvé pronto.</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="eventsGrid">
        <?php foreach ($events as $ev):
            $evStatus = eventStatus($ev);
            $evStatusColor = eventStatusColor($evStatus);
            $evDataStatus = strtolower(str_replace(' ', '-', $evStatus));
        ?>
        <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow event-card"
                 data-status="<?= $evDataStatus ?>">
            <!-- Imagen de portada -->
            <div class="aspect-video bg-color-lighter overflow-hidden">
                <?php if ($ev['cover_image']): ?>
                <img src="<?= APP_URL ?>/<?= htmlspecialchars($ev['cover_image'], ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?>"
                     class="w-full h-full object-cover">
                <?php else: ?>
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-color-lighter" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <?php endif; ?>
            </div>

            <!-- Contenido -->
            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <h2 class="text-lg font-semibold text-gray-800 leading-tight">
                        <?= htmlspecialchars($ev['title'], ENT_QUOTES, 'UTF-8') ?>
                    </h2>
                    <span class="flex-shrink-0 inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $evStatusColor ?>">
                        <?= $evStatus ?>
                    </span>
                </div>

                <?php if ($ev['start_date']): ?>
                <div class="flex items-center gap-1.5 text-sm text-gray-500 mb-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <?php
                    $dias = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
                    $meses = ['January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril','May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto','September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'];
                    $ts = strtotime($ev['start_date']);
                    $diaNombre = $dias[date('l', $ts)];
                    $mesNombre = $meses[date('F', $ts)];
                    echo "{$diaNombre} " . date('j', $ts) . " de {$mesNombre}, " . date('Y', $ts) . " · " . date('H:i', $ts) . " hs";
                    ?>
                </div>
                <?php endif; ?>

                <?php if ($ev['location']): ?>
                <div class="flex items-center gap-1.5 text-sm text-gray-500 mb-3">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <?= htmlspecialchars($ev['location'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php endif; ?>

                <?php if ($ev['max_capacity']): ?>
                <div class="text-xs text-gray-400 mb-3">
                    <?= (int)$ev['total_submissions'] ?> / <?= (int)$ev['max_capacity'] ?> inscripciones
                    <?php if ($ev['total_submissions'] >= $ev['max_capacity']): ?>
                    — <span class="text-red-500 font-medium">Cupo completo</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <a href="<?= APP_URL ?>/evento/<?= htmlspecialchars($ev['slug'], ENT_QUOTES, 'UTF-8') ?>"
                   class="block w-full text-center px-4 py-2.5 bg-color-secondary hover:bg-color-secondary text-white rounded-xl text-sm font-medium transition-colors">
                    Ver más e inscribirse
                </a>
            </div>
        </article>
        <?php endforeach; ?>
    </div>

    <?= $paginator->render() ?>
    <?php endif; ?>
</section>

<script>
function filterEvents(type) {
    const cards = document.querySelectorAll('.event-card');
    cards.forEach(card => {
        const status = card.dataset.status;
        if (type === 'all') {
            card.style.display = '';
        } else if (type === 'upcoming' && status === 'próximo') {
            card.style.display = '';
        } else if (type === 'ongoing' && status === 'en-curso') {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
}
</script>
