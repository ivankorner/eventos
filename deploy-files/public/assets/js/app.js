/**
 * JavaScript global de la aplicación
 * Utilidades compartidas entre todas las vistas del admin
 */

// Confirmación de eliminación con mensaje personalizable
function confirmDelete(message, formEl) {
    if (confirm(message || '¿Estás seguro? Esta acción no se puede deshacer.')) {
        formEl.submit();
    }
    return false;
}

// Auto-dismiss de alertas flash después de 5 segundos
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('[role="alert"]');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity    = '0';
            setTimeout(() => alert.remove(), 500);
        }, 5000);
    });
});

// Copiar texto al portapapeles (para URLs de formularios)
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 right-4 bg-gray-800 text-white text-sm px-4 py-2 rounded-lg shadow-lg z-50';
        toast.textContent = 'Copiado al portapapeles';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    });
}

// Generar slug desde título (para el formulario de eventos)
document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput  = document.querySelector('input[name="slug"]');

    if (titleInput && slugInput) {
        let userEditedSlug = slugInput.value !== '';

        slugInput.addEventListener('input', () => {
            userEditedSlug = true;
        });

        titleInput.addEventListener('input', () => {
            if (!userEditedSlug) {
                slugInput.value = slugify(titleInput.value);
            }
        });
    }
});

function slugify(text) {
    return text
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}
