<?php
$dias  = ['Sunday'=>'Domingo','Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles','Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado'];
$meses = ['January'=>'enero','February'=>'febrero','March'=>'marzo','April'=>'abril','May'=>'mayo','June'=>'junio','July'=>'julio','August'=>'agosto','September'=>'septiembre','October'=>'octubre','November'=>'noviembre','December'=>'diciembre'];

function formatDateEs(string $dt, array $dias, array $meses): string {
    $ts = strtotime($dt);
    return $dias[date('l', $ts)] . ' ' . date('j', $ts) . ' de ' . $meses[date('F', $ts)] . ', ' . date('Y', $ts) . ' · ' . date('H:i', $ts) . ' hs';
}
?>
<!-- Imagen de portada -->
<?php if ($event['cover_image']): ?>
<div class="w-full h-64 sm:h-80 lg:h-96 bg-color-primary overflow-hidden">
    <img src="<?= APP_URL ?>/<?= htmlspecialchars($event['cover_image'], ENT_QUOTES, 'UTF-8') ?>"
         alt="<?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>"
         class="w-full h-full object-cover opacity-80">
</div>
<?php endif; ?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-10">

    <!-- Datos del evento -->
    <div class="mb-8">
        <a href="<?= APP_URL ?>/" class="text-sm text-color-secondary hover:underline">← Volver a todos los eventos</a>
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mt-4 mb-4">
            <?= htmlspecialchars($event['title'], ENT_QUOTES, 'UTF-8') ?>
        </h1>

        <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
            <?php if ($event['start_date']): ?>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-color-lighter" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <?= formatDateEs($event['start_date'], $dias, $meses) ?>
            </div>
            <?php endif; ?>
            <?php if ($event['location']): ?>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-color-lighter" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <?= htmlspecialchars($event['location'], ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
            <?php if ($event['max_capacity']): ?>
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-color-lighter" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <?= max(0, (int)$event['max_capacity'] - (int)$totalSubs) ?> cupos disponibles
            </div>
            <?php endif; ?>
        </div>

        <?php if ($event['description']): ?>
        <div class="prose prose-blue max-w-none text-gray-700 leading-relaxed">
            <?= $event['description'] /* El admin ingresó HTML — no escapar */ ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Formulario de inscripción -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Inscribirse al evento</h2>

        <?php if (!$canSubmit): ?>
        <!-- Mensaje cuando no se puede inscribir -->
        <div class="text-center py-8 text-gray-500">
            <?php if ($isFull): ?>
            <p class="text-lg font-semibold text-red-600 mb-2">Cupo agotado</p>
            <p class="text-sm">No quedan lugares disponibles para este evento.</p>
            <?php elseif ($isExpired): ?>
            <p class="text-lg font-semibold text-gray-600 mb-2">Inscripciones cerradas</p>
            <p class="text-sm">El plazo de inscripción para este evento ya finalizó.</p>
            <?php else: ?>
            <p class="text-sm">Las inscripciones para este evento no están disponibles en este momento.</p>
            <?php endif; ?>
        </div>

        <?php else: ?>

        <!-- Errores de validación del backend -->
        <?php if (!empty($errors)): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <h3 class="font-medium text-red-800 mb-2">Por favor corregí los siguientes errores:</h3>
            <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
                <?php foreach ($errors as $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- El formulario funciona sin JS (progressive enhancement) -->
        <form action="<?= APP_URL ?>/evento/<?= htmlspecialchars($event['slug'], ENT_QUOTES, 'UTF-8') ?>/inscribirse"
              method="POST"
              enctype="multipart/form-data"
              x-data="formValidation()"
              @submit.prevent="submitForm($el)"
              novalidate>

            <?= $csrf ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5" id="formFields">
            <?php foreach ($formFields as $field):
                $fieldId  = $field['id'];
                $label    = htmlspecialchars($field['label'] ?? '', ENT_QUOTES, 'UTF-8');
                $required = !empty($field['required']);
                $oldVal   = $old[$fieldId] ?? '';
                $hasError = !empty($errors[$fieldId]);
                $width    = $field['width'] ?? 'full';
                $colClass = $width === 'half' ? '' : 'sm:col-span-2';
            ?>
            <div class="<?= $colClass ?> form-field" data-field-type="<?= $field['type'] ?>">

                <?php if ($field['type'] === 'heading'): ?>
                <h3 class="text-lg font-bold text-gray-800 sm:col-span-2 border-b border-gray-200 pb-2">
                    <?= $label ?>
                </h3>

                <?php elseif ($field['type'] === 'paragraph'): ?>
                <p class="text-sm text-gray-600 sm:col-span-2">
                    <?= htmlspecialchars($field['help_text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php else: ?>

                <label for="field_<?= $fieldId ?>" class="block text-sm font-medium text-gray-700 mb-1">
                    <?= $label ?>
                    <?php if ($required): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
                </label>

                <?php
                $inputClass = 'w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-color-lighter transition-colors ' .
                              ($hasError ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-white');
                ?>

                <?php if ($field['type'] === 'textarea'): ?>
                <textarea name="<?= $fieldId ?>" id="field_<?= $fieldId ?>"
                          rows="<?= $field['rows'] ?? 3 ?>"
                          <?= $required ? 'required' : '' ?>
                          class="<?= $inputClass ?>"
                          placeholder="<?= htmlspecialchars($field['placeholder'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($oldVal, ENT_QUOTES, 'UTF-8') ?></textarea>

                <?php elseif ($field['type'] === 'select'): ?>
                <select name="<?= $fieldId ?>" id="field_<?= $fieldId ?>"
                        <?= $required ? 'required' : '' ?>
                        class="<?= $inputClass ?>">
                    <option value="">Seleccioná una opción...</option>
                    <?php foreach ($field['options'] ?? [] as $opt): ?>
                    <option value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>"
                            <?= $oldVal === $opt['value'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                    <?php endforeach; ?>
                </select>

                <?php elseif ($field['type'] === 'checkbox'): ?>
                <div class="space-y-2">
                    <?php foreach ($field['options'] ?? [] as $opt):
                        $checked = is_array($oldVal) ? in_array($opt['value'], $oldVal) : false;
                    ?>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="checkbox" name="<?= $fieldId ?>[]"
                               value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>"
                               <?= $checked ? 'checked' : '' ?>
                               class="w-4 h-4 text-color-secondary rounded border-gray-300 focus:ring-color-lighter">
                        <?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif ($field['type'] === 'radio'): ?>
                <div class="space-y-2">
                    <?php foreach ($field['options'] ?? [] as $opt):
                        $checked = $oldVal === $opt['value'];
                    ?>
                    <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <input type="radio" name="<?= $fieldId ?>"
                               value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>"
                               <?= $required ? 'required' : '' ?>
                               <?= $checked ? 'checked' : '' ?>
                               class="w-4 h-4 text-color-secondary border-gray-300 focus:ring-color-lighter">
                        <?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?>
                    </label>
                    <?php endforeach; ?>
                </div>

                <?php elseif ($field['type'] === 'file'): ?>
                <input type="file" name="<?= $fieldId ?>" id="field_<?= $fieldId ?>"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-color-lighter file:text-color-secondary hover:file:bg-color-lighter">

                <?php else: ?>
                <!-- text, email, tel, number, date, url -->
                <input type="<?= $field['type'] ?>"
                       name="<?= $fieldId ?>"
                       id="field_<?= $fieldId ?>"
                       value="<?= htmlspecialchars($oldVal, ENT_QUOTES, 'UTF-8') ?>"
                       <?= $required ? 'required' : '' ?>
                       <?php if (!empty($field['validations']['min_length'])): ?>minlength="<?= (int)$field['validations']['min_length'] ?>"<?php endif; ?>
                       <?php if (!empty($field['validations']['max_length'])): ?>maxlength="<?= (int)$field['validations']['max_length'] ?>"<?php endif; ?>
                       placeholder="<?= htmlspecialchars($field['placeholder'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       class="<?= $inputClass ?>">
                <?php endif; ?>

                <?php if ($hasError): ?>
                <p class="text-red-500 text-xs mt-1"><?= htmlspecialchars($errors[$fieldId][0], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <?php if (!empty($field['help_text']) && !in_array($field['type'], ['heading','paragraph'])): ?>
                <p class="text-gray-400 text-xs mt-1"><?= htmlspecialchars($field['help_text'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>

                <?php endif; // tipos distintos a heading/paragraph ?>
            </div>
            <?php endforeach; ?>
            </div>

            <!-- Botón de envío -->
            <div class="mt-8">
                <button type="submit"
                        class="w-full sm:w-auto px-8 py-3 bg-color-secondary hover:bg-color-secondary disabled:opacity-60 text-white font-semibold rounded-xl text-base transition-colors"
                        x-bind:disabled="submitting">
                    <span x-show="!submitting">
                        <?= htmlspecialchars($formSettings['submit_label'] ?? 'Enviar inscripción', ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <span x-show="submitting" x-cloak>Enviando...</span>
                </button>
            </div>
        </form>

        <?php endif; // canSubmit ?>
    </div>
</div>

<script>
function formValidation() {
    return {
        submitting: false,
        submitForm(form) {
            // Validación básica en frontend antes de enviar
            const required = form.querySelectorAll('[required]');
            let valid = true;
            required.forEach(el => {
                if (!el.value.trim()) {
                    el.classList.add('border-red-400', 'bg-red-50');
                    valid = false;
                } else {
                    el.classList.remove('border-red-400', 'bg-red-50');
                }
            });
            if (valid) {
                this.submitting = true;
                form.submit();
            }
        }
    };
}
</script>
