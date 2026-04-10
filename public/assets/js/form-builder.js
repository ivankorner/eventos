/**
 * Constructor de formularios drag & drop
 * Usa Alpine.js + SortableJS
 * Los datos se leen de FORM_BUILDER_DATA definido en la vista PHP
 */

document.addEventListener('alpine:init', () => {
    Alpine.data('formBuilder', () => ({
        fields: [],
        formTitle: 'Formulario',
        selectedFieldId: null,
        previewOpen: false,
        saveStatus: '',
        hasUnsavedChanges: false,
        autosaveInterval: null,

        get selectedField() {
            return this.fields.find(f => f.id === this.selectedFieldId) || null;
        },

        // -------------------------
        // Inicialización
        // -------------------------
        init() {
            // Cargar datos existentes del formulario (desde FORM_BUILDER_DATA)
            const data = window.FORM_BUILDER_DATA;
            if (data && data.formJson) {
                const json = typeof data.formJson === 'string'
                    ? JSON.parse(data.formJson)
                    : data.formJson;
                this.fields     = json.fields || [];
                this.formTitle  = json.settings?.submit_label ? this.formTitle : this.formTitle;
            }

            // Configurar drag & drop desde el panel de tipos al canvas
            this.$nextTick(() => {
                this.setupDragFromTypesPanel();
                this.setupSortableCanvas();
            });

            // Autosave cada 60 segundos
            this.autosaveInterval = setInterval(() => {
                if (this.hasUnsavedChanges) {
                    this.saveForm(false, true); // true = autosave silencioso
                }
            }, 60000);

            // Marcar cambios al modificar los fields
            this.$watch('fields', () => {
                this.hasUnsavedChanges = true;
                this.saveStatus = 'Cambios sin guardar';
                // Actualizar la vista previa si está abierta
                if (this.previewOpen) {
                    this.$nextTick(() => this.renderPreview());
                }
            }, { deep: true });

            this.$watch('previewOpen', (val) => {
                if (val) this.$nextTick(() => this.renderPreview());
            });
        },

        // -------------------------
        // Tipos de campo disponibles (para crear campos nuevos)
        // -------------------------
        fieldDefaults(type) {
            const base = {
                id:         'field_' + this.generateUUID(),
                type:       type,
                label:      this.fieldTypeLabel(type),
                required:   false,
                help_text:  '',
                width:      'full',
                order:      this.fields.length + 1,
                placeholder: '',
                validations: {},
            };

            // Valores por defecto según tipo
            if (['select', 'checkbox', 'radio'].includes(type)) {
                base.options = [
                    { value: 'opcion-1', label: 'Opción 1' },
                    { value: 'opcion-2', label: 'Opción 2' },
                ];
            }
            if (type === 'textarea') {
                base.rows = 3;
            }
            if (type === 'number') {
                base.validations = { min: null, max: null, step: 1 };
            }
            if (type === 'text') {
                base.validations = { min_length: null, max_length: null };
            }

            return base;
        },

        fieldTypeLabel(type) {
            const labels = {
                text:      'Texto corto',
                textarea:  'Texto largo',
                email:     'Email',
                tel:       'Teléfono',
                number:    'Número',
                date:      'Fecha',
                url:       'URL',
                select:    'Selección única',
                checkbox:  'Selección múltiple',
                radio:     'Opción única',
                file:      'Archivo adjunto',
                heading:   'Título / Separador',
                paragraph: 'Párrafo informativo',
            };
            return labels[type] || type;
        },

        // -------------------------
        // Agregar / eliminar campos
        // -------------------------
        addField(type) {
            const field = this.fieldDefaults(type);
            this.fields.push(field);
            this.selectedFieldId = field.id;
            this.updateOrder();
        },

        removeField(id) {
            if (!confirm('¿Eliminás este campo? Los datos de inscripciones ya existentes quedarán sin etiqueta para este campo.')) {
                return;
            }
            this.fields = this.fields.filter(f => f.id !== id);
            if (this.selectedFieldId === id) {
                this.selectedFieldId = null;
            }
            this.updateOrder();
        },

        selectField(id) {
            this.selectedFieldId = this.selectedFieldId === id ? null : id;
        },

        updateOrder() {
            this.fields.forEach((f, i) => { f.order = i + 1; });
        },

        // -------------------------
        // Drag & Drop desde panel de tipos
        // -------------------------
        setupDragFromTypesPanel() {
            const fieldTypeItems = document.querySelectorAll('.field-type-item');
            fieldTypeItems.forEach(item => {
                // Remover listeners previos para evitar duplicados
                const newItem = item.cloneNode(true);
                item.parentNode.replaceChild(newItem, item);

                // Agregar nuevo listener
                newItem.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.addField(newItem.dataset.type);
                    this.$nextTick(() => this.setupSortableCanvas());
                });
            });
        },

        // -------------------------
        // Drag & Drop para reordenar campos en el canvas
        // -------------------------
        setupSortableCanvas() {
            const canvas = document.getElementById('sortableCanvas');
            if (!canvas || !window.Sortable) return;

            if (canvas._sortable) {
                canvas._sortable.destroy();
            }

            canvas._sortable = new Sortable(canvas, {
                group:     'formCanvas',
                handle:    '.drag-handle',
                animation: 150,
                onEnd: (evt) => {
                    // Reordenar el array fields según el nuevo orden DOM
                    const newOrder = [...canvas.querySelectorAll('[data-field-id]')]
                        .map(el => el.dataset.fieldId);

                    const reordered = newOrder
                        .map(id => this.fields.find(f => f.id === id))
                        .filter(Boolean);

                    this.fields = reordered;
                    this.updateOrder();
                },
            });
        },

        // -------------------------
        // Serialización y guardado
        // -------------------------
        buildFormJson(activate) {
            const data = window.FORM_BUILDER_DATA;
            const existingJson = data?.formJson || {};
            const existing = typeof existingJson === 'string'
                ? JSON.parse(existingJson)
                : existingJson;

            return {
                version:  '1.0',
                settings: existing.settings || {
                    submit_label:    'Enviar inscripción',
                    success_message: '¡Gracias! Tu inscripción fue recibida.',
                    notify_email:    '',
                    max_submissions: null,
                    allow_duplicates: false,
                },
                fields: this.fields,
            };
        },

        async saveForm(activate = false, silent = false) {
            const data    = window.FORM_BUILDER_DATA;
            const formJson = JSON.stringify(this.buildFormJson(activate));

            this.saveStatus = 'Guardando...';

            try {
                const formData = new FormData();
                formData.append('fields_json',  formJson);
                formData.append('form_title',   this.formTitle);
                formData.append('_csrf_token',  data.csrfToken);
                if (activate) {
                    formData.append('activate', '1');
                }

                const response = await fetch(data.saveUrl, {
                    method:  'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body:    formData,
                });

                const result = await response.json();

                if (result.success) {
                    this.hasUnsavedChanges = false;
                    this.saveStatus = silent
                        ? `Guardado automáticamente a las ${result.saved_at}`
                        : `Guardado a las ${result.saved_at}`;
                    if (!silent && activate) {
                        this.saveStatus += ' — Formulario activado';
                    }
                } else {
                    this.saveStatus = '⚠ Error al guardar: ' + (result.message || 'Error desconocido');
                }
            } catch (e) {
                this.saveStatus = '⚠ Error de red al guardar';
                console.error('Error guardando formulario:', e);
            }
        },

        // -------------------------
        // Vista previa
        // -------------------------
        renderPreview() {
            const container = document.getElementById('previewContainer');
            if (!container) return;

            let html = '<form class="space-y-4" onsubmit="return false;">';
            html += '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">';

            this.fields.forEach(field => {
                const colClass = field.width === 'half' ? '' : 'sm:col-span-2';
                html += `<div class="${colClass}">`;

                if (field.type === 'heading') {
                    html += `<h3 class="text-lg font-bold text-gray-800 border-b border-gray-200 pb-2">${this.escHtml(field.label)}</h3>`;
                } else if (field.type === 'paragraph') {
                    html += `<p class="text-sm text-gray-600">${this.escHtml(field.help_text || '')}</p>`;
                } else {
                    const required = field.required ? '<span class="text-red-500 ml-0.5">*</span>' : '';
                    html += `<label class="block text-sm font-medium text-gray-700 mb-1">${this.escHtml(field.label)}${required}</label>`;

                    const inputClass = 'w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-gray-50';

                    if (['text','email','tel','number','url'].includes(field.type)) {
                        html += `<input type="${field.type}" placeholder="${this.escHtml(field.placeholder || '')}" class="${inputClass}" disabled>`;
                    } else if (field.type === 'date') {
                        html += `<input type="date" class="${inputClass}" disabled>`;
                    } else if (field.type === 'textarea') {
                        html += `<textarea rows="3" placeholder="${this.escHtml(field.placeholder || '')}" class="${inputClass}" disabled></textarea>`;
                    } else if (field.type === 'select') {
                        html += `<select class="${inputClass}" disabled><option>Seleccioná...</option>`;
                        (field.options || []).forEach(o => {
                            html += `<option>${this.escHtml(o.label)}</option>`;
                        });
                        html += '</select>';
                    } else if (['checkbox','radio'].includes(field.type)) {
                        (field.options || []).forEach(o => {
                            html += `<label class="flex items-center gap-2 text-sm text-gray-700 mb-1 cursor-not-allowed">
                                <input type="${field.type}" disabled class="w-4 h-4"> ${this.escHtml(o.label)}
                            </label>`;
                        });
                    } else if (field.type === 'file') {
                        html += `<div class="border-2 border-dashed border-gray-300 rounded-lg px-4 py-6 text-center text-sm text-gray-400">
                            Subir archivo (deshabilitado en la vista previa)
                        </div>`;
                    }

                    if (field.help_text) {
                        html += `<p class="text-xs text-gray-400 mt-1">${this.escHtml(field.help_text)}</p>`;
                    }
                }

                html += '</div>';
            });

            html += '</div>';
            html += `<button class="mt-4 px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-medium opacity-60 cursor-not-allowed" disabled>
                Enviar inscripción (vista previa)
            </button>`;
            html += '</form>';

            container.innerHTML = html;
        },

        // -------------------------
        // Utilidades
        // -------------------------
        generateUUID() {
            if (crypto && crypto.randomUUID) {
                return crypto.randomUUID().replace(/-/g, '').substring(0, 16);
            }
            // Polyfill simple para navegadores viejos
            return Math.random().toString(36).substring(2, 10) + Date.now().toString(36);
        },

        slugify(text) {
            return text
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Quitar tildes
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-') || 'opcion';
        },

        escHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },
    }));
});
