<!-- Constructor de formularios drag & drop -->
<!-- SortableJS y lógica en /public/assets/js/form-builder.js -->
<div class="mt-2 -mx-4 sm:-mx-6" x-data="formBuilder()" x-init="init()">

    <!-- Barra superior del constructor -->
    <div class="sticky top-14 z-20 bg-white border-b border-gray-200 px-4 sm:px-6 py-3 flex flex-col sm:flex-row items-start sm:items-center gap-3 justify-between">
        <div class="flex items-center gap-3 flex-1 min-w-0">
            <input type="text" x-model="formTitle"
                   class="text-lg font-semibold text-gray-800 border-none focus:outline-none focus:ring-2 focus:ring-color-light rounded px-2 py-1 flex-1 min-w-0"
                   placeholder="Nombre del formulario">
            <span class="text-xs text-gray-400 flex-shrink-0" x-text="saveStatus"></span>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="previewOpen = !previewOpen"
                    class="px-3 py-1.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50 transition-colors">
                Vista previa
            </button>
            <button @click="saveForm(false)"
                    class="px-3 py-1.5 border border-color-light text-color-secondary rounded-lg text-sm hover:bg-color-lighter transition-colors">
                Guardar borrador
            </button>
            <button @click="saveForm(true)"
                    class="px-4 py-1.5 bg-color-secondary hover:bg-color-secondary text-white rounded-lg text-sm font-medium transition-colors">
                Guardar y activar
            </button>
        </div>
    </div>

    <!-- Área principal del constructor -->
    <div class="flex h-[calc(100vh-13rem)] overflow-hidden">

        <!-- Panel izquierdo: tipos de campo -->
        <div class="w-56 flex-shrink-0 bg-gray-50 border-r border-gray-200 overflow-y-auto p-3">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 px-1">Tipos de campo</p>
            <div id="fieldTypes" class="space-y-1">
                <?php
                $fieldTypes = [
                    ['type' => 'text',      'label' => 'Texto corto'],
                    ['type' => 'textarea',  'label' => 'Texto largo'],
                    ['type' => 'email',     'label' => 'Email'],
                    ['type' => 'tel',       'label' => 'Teléfono'],
                    ['type' => 'number',    'label' => 'Número'],
                    ['type' => 'date',      'label' => 'Fecha'],
                    ['type' => 'url',       'label' => 'URL'],
                    ['type' => 'select',    'label' => 'Selección única'],
                    ['type' => 'checkbox',  'label' => 'Selección múltiple'],
                    ['type' => 'radio',     'label' => 'Opción única'],
                    ['type' => 'file',      'label' => 'Archivo adjunto'],
                    ['type' => 'heading',   'label' => 'Título / Separador'],
                    ['type' => 'paragraph', 'label' => 'Párrafo informativo'],
                ];
                foreach ($fieldTypes as $ft):
                ?>
                <div class="field-type-item flex items-center gap-2 px-3 py-2 bg-white border border-gray-200 rounded-lg cursor-grab hover:border-color-light hover:bg-color-lighter transition-colors text-sm text-gray-700 select-none"
                     data-type="<?= $ft['type'] ?>">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <?= $ft['label'] ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Área central: canvas del formulario -->
        <div class="flex-1 overflow-y-auto bg-gray-100 p-4">
            <div id="formCanvas"
                 class="min-h-full max-w-2xl mx-auto"
                 :class="fields.length === 0 ? 'flex items-center justify-center' : ''">

                <!-- Placeholder cuando no hay campos -->
                <div x-show="fields.length === 0" class="text-center text-gray-400 py-16">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-sm">Arrastrá campos desde el panel izquierdo o hacé clic en ellos para agregarlos</p>
                </div>

                <!-- Lista de campos del formulario -->
                <div id="sortableCanvas" class="space-y-2">
                    <template x-for="(field, index) in fields" :key="field.id">
                        <div class="field-card bg-white border-2 rounded-xl p-4 cursor-pointer transition-all"
                             :class="selectedFieldId === field.id ? 'border-color-lighter shadow-md' : 'border-transparent hover:border-gray-200'"
                             @click="selectField(field.id)"
                             :data-field-id="field.id">

                            <!-- Handle de drag -->
                            <div class="flex items-start gap-3">
                                <div class="drag-handle flex-shrink-0 mt-0.5 cursor-grab text-gray-300 hover:text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>

                                <!-- Preview del campo -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span class="text-sm font-medium text-gray-700" x-text="field.label || '(sin etiqueta)'"></span>
                                        <span x-show="field.required" class="text-red-500 text-xs">*</span>
                                        <span class="text-xs text-gray-400 ml-auto" x-text="fieldTypeLabel(field.type)"></span>
                                    </div>

                                    <!-- Preview visual simplificado por tipo -->
                                    <div class="text-sm text-gray-400">
                                        <template x-if="['text','email','tel','url','number'].includes(field.type)">
                                            <div class="border border-gray-200 rounded px-3 py-1.5 bg-gray-50 text-gray-300 text-xs"
                                                 x-text="field.placeholder || 'Escribir aquí...'"></div>
                                        </template>
                                        <template x-if="field.type === 'textarea'">
                                            <div class="border border-gray-200 rounded px-3 py-2 bg-gray-50 text-gray-300 text-xs h-12 flex items-start"
                                                 x-text="field.placeholder || 'Texto largo...'"></div>
                                        </template>
                                        <template x-if="field.type === 'date'">
                                            <div class="border border-gray-200 rounded px-3 py-1.5 bg-gray-50 text-gray-300 text-xs">dd/mm/aaaa</div>
                                        </template>
                                        <template x-if="field.type === 'select'">
                                            <div class="border border-gray-200 rounded px-3 py-1.5 bg-gray-50 text-gray-300 text-xs flex items-center justify-between">
                                                <span x-text="(field.options || []).length ? field.options[0].label : 'Opción...'"></span>
                                                <span>▾</span>
                                            </div>
                                        </template>
                                        <template x-if="['checkbox','radio'].includes(field.type)">
                                            <div class="space-y-1">
                                                <template x-for="opt in (field.options || []).slice(0, 2)" :key="opt.value">
                                                    <div class="flex items-center gap-2 text-xs text-gray-400">
                                                        <span class="w-3 h-3 border border-gray-300 rounded flex-shrink-0"
                                                              :class="field.type === 'radio' ? 'rounded-full' : ''"></span>
                                                        <span x-text="opt.label"></span>
                                                    </div>
                                                </template>
                                                <span x-show="(field.options || []).length > 2" class="text-xs text-gray-300"
                                                      x-text="'+ ' + ((field.options || []).length - 2) + ' más'"></span>
                                            </div>
                                        </template>
                                        <template x-if="field.type === 'heading'">
                                            <h3 class="text-base font-bold text-gray-700" x-text="field.label || 'Título de sección'"></h3>
                                        </template>
                                        <template x-if="field.type === 'paragraph'">
                                            <p class="text-xs text-gray-500" x-text="field.help_text || 'Texto informativo...'"></p>
                                        </template>
                                        <template x-if="field.type === 'file'">
                                            <div class="border-2 border-dashed border-gray-200 rounded px-3 py-2 text-center text-xs text-gray-300">
                                                Subir archivo...
                                            </div>
                                        </template>
                                    </div>

                                    <p class="text-xs text-gray-400 mt-1.5" x-show="field.help_text && !['heading','paragraph'].includes(field.type)"
                                       x-text="field.help_text"></p>
                                </div>

                                <!-- Botón eliminar -->
                                <button @click.stop="removeField(field.id)"
                                        class="flex-shrink-0 text-gray-300 hover:text-red-500 transition-colors ml-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Panel derecho: configuración del campo seleccionado -->
        <div class="w-72 flex-shrink-0 bg-white border-l border-gray-200 overflow-y-auto"
             x-show="selectedFieldId !== null" x-cloak>
            <div class="p-4" x-show="selectedField">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-700 text-sm">Configurar campo</h3>
                    <button @click="selectedFieldId = null" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <template x-if="selectedField">
                    <div class="space-y-4">

                        <!-- Label -->
                        <template x-if="!['heading', 'paragraph'].includes(selectedField.type)">
                        <div>
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Etiqueta del campo</label>
                            <input type="text" x-model="selectedField.label"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                        </div>
                        </template>

                        <!-- Heading text -->
                        <template x-if="selectedField.type === 'heading'">
                        <div>
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Texto del título</label>
                            <input type="text" x-model="selectedField.label"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                        </div>
                        </template>

                        <!-- Placeholder -->
                        <template x-if="['text','textarea','email','tel','number','url','date'].includes(selectedField.type)">
                        <div>
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Placeholder</label>
                            <input type="text" x-model="selectedField.placeholder"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                        </div>
                        </template>

                        <!-- Required -->
                        <template x-if="!['heading','paragraph'].includes(selectedField.type)">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-medium text-gray-600">Campo obligatorio</label>
                            <button @click="selectedField.required = !selectedField.required"
                                    class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors"
                                    :class="selectedField.required ? 'bg-color-secondary' : 'bg-gray-200'">
                                <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white transition-transform"
                                      :class="selectedField.required ? 'translate-x-4' : 'translate-x-1'"></span>
                            </button>
                        </div>
                        </template>

                        <!-- Ancho del campo -->
                        <template x-if="!['heading','paragraph'].includes(selectedField.type)">
                        <div>
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Ancho</label>
                            <select x-model="selectedField.width"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                                <option value="full">Ancho completo</option>
                                <option value="half">Mitad</option>
                            </select>
                        </div>
                        </template>

                        <!-- Texto de ayuda -->
                        <div>
                            <label class="text-xs font-medium text-gray-600 mb-1 block">Texto de ayuda</label>
                            <input type="text" x-model="selectedField.help_text"
                                   placeholder="Aparece debajo del campo"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-color-light">
                        </div>

                        <!-- Validaciones: min/max longitud para texto -->
                        <template x-if="['text','textarea'].includes(selectedField.type)">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-xs text-gray-600 mb-1 block">Mín. caracteres</label>
                                <input type="number" min="0" x-model.number="selectedField.validations.min_length"
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-color-light">
                            </div>
                            <div>
                                <label class="text-xs text-gray-600 mb-1 block">Máx. caracteres</label>
                                <input type="number" min="0" x-model.number="selectedField.validations.max_length"
                                       class="w-full px-2 py-1.5 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-color-light">
                            </div>
                        </div>
                        </template>

                        <!-- Opciones para select/checkbox/radio -->
                        <template x-if="['select','checkbox','radio'].includes(selectedField.type)">
                        <div>
                            <label class="text-xs font-medium text-gray-600 mb-2 block">Opciones</label>
                            <div class="space-y-2">
                                <template x-for="(opt, i) in selectedField.options" :key="i">
                                    <div class="flex items-center gap-2">
                                        <input type="text" x-model="opt.label" placeholder="Etiqueta"
                                               @input="opt.value = slugify(opt.label)"
                                               class="flex-1 px-2 py-1 border border-gray-300 rounded text-xs focus:outline-none focus:ring-1 focus:ring-color-light">
                                        <button @click="selectedField.options.splice(i, 1)" class="text-red-400 hover:text-red-600 flex-shrink-0">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </template>
                            </div>
                            <button @click="selectedField.options.push({ value: 'opcion-' + (selectedField.options.length + 1), label: 'Opción ' + (selectedField.options.length + 1) })"
                                    class="mt-2 w-full text-center text-xs text-color-secondary hover:text-color-secondary border border-dashed border-color-light rounded py-1.5 transition-colors">
                                + Agregar opción
                            </button>
                        </div>
                        </template>

                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- Modal de vista previa -->
    <div x-show="previewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="font-semibold text-gray-800">Vista previa del formulario</h3>
                <button @click="previewOpen = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <div id="previewContainer" class="form-preview">
                    <!-- Renderizado por JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Datos para el constructor JS -->
<script>
    window.FORM_BUILDER_DATA = {
        eventId:   <?= (int)$event['id'] ?>,
        saveUrl:   '<?= APP_URL ?>/admin/eventos/<?= (int)$event['id'] ?>/formulario/guardar',
        csrfToken: '<?= Csrf::token() ?>',
        formJson:  <?= $formJson ?>
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="<?= APP_URL ?>/assets/js/form-builder.js"></script>
