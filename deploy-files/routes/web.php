<?php
/**
 * Definición de todas las rutas de la aplicación
 * Se registran en el Router y se despachan desde public/index.php
 */

// =====================================================
// Rutas públicas
// =====================================================
$router->get('/', [PublicController::class, 'index']);
$router->get('/evento/{slug}', [PublicController::class, 'show']);
$router->post('/evento/{slug}/inscribirse', [PublicController::class, 'submit'],
    [RateLimitMiddleware::class]);
$router->get('/inscripcion/confirmacion', [PublicController::class, 'confirmation']);

// =====================================================
// Autenticación
// =====================================================
$router->get('/admin/login',   [AuthController::class, 'loginForm']);
$router->post('/admin/login',  [AuthController::class, 'login']);
$router->get('/admin/logout',  [AuthController::class, 'logout']);

// Cambiar contraseña (autenticado pero con must_change_password)
$router->get('/admin/cambiar-password',  [AuthController::class, 'changePasswordForm'],
    [AuthMiddleware::class]);
$router->post('/admin/cambiar-password', [AuthController::class, 'changePassword'],
    [AuthMiddleware::class]);

// =====================================================
// Área admin — requiere autenticación
// =====================================================
$router->group('/admin', [AuthMiddleware::class], function (GroupRouter $r) {

    // Dashboard
    $r->get('/dashboard', [DashboardController::class, 'index']);

    // ---- Eventos ----
    $r->get('/eventos',                          [EventController::class, 'index']);
    $r->get('/eventos/crear',                    [EventController::class, 'create']);
    $r->post('/eventos/crear',                   [EventController::class, 'store']);
    $r->get('/eventos/{id}/editar',              [EventController::class, 'edit']);
    $r->post('/eventos/{id}/editar',             [EventController::class, 'update']);
    $r->post('/eventos/{id}/eliminar',           [EventController::class, 'destroy']);
    $r->post('/eventos/{id}/duplicar',           [EventController::class, 'duplicate']);
    $r->post('/eventos/{id}/estado',             [EventController::class, 'changeStatus']);

    // ---- Constructor de formularios ----
    $r->get('/eventos/{id}/formulario',          [FormController::class, 'builder']);
    $r->post('/eventos/{id}/formulario/guardar', [FormController::class, 'save']);

    // ---- Inscripciones ----
    $r->get('/inscripciones',                    [SubmissionController::class, 'allSubmissions']);
    $r->get('/eventos/{id}/inscripciones',       [SubmissionController::class, 'index']);
    $r->get('/inscripciones/{id}',               [SubmissionController::class, 'show']);
    $r->post('/inscripciones/{id}/estado',       [SubmissionController::class, 'updateStatus']);
    $r->post('/inscripciones/{id}/eliminar',     [SubmissionController::class, 'destroy']);

    // ---- Exportaciones ----
    $r->get('/eventos/{id}/exportar/excel',      [ExportController::class, 'excel']);
    $r->get('/eventos/{id}/exportar/csv',        [ExportController::class, 'csv']);
    $r->get('/eventos/{id}/exportar/pdf',        [ExportController::class, 'pdfEvent']);
    $r->get('/inscripciones/{id}/exportar/pdf',  [ExportController::class, 'pdfSubmission']);

    // ---- Usuarios (solo super_admin) ----
    $r->get('/usuarios',             [UserController::class, 'index'],       [SuperAdminMiddleware::class]);
    $r->get('/usuarios/crear',       [UserController::class, 'create'],      [SuperAdminMiddleware::class]);
    $r->post('/usuarios/crear',      [UserController::class, 'store'],       [SuperAdminMiddleware::class]);
    $r->post('/usuarios/{id}/activar',[UserController::class, 'toggleActive'],[SuperAdminMiddleware::class]);

    // ---- Configuración (solo super_admin) ----
    $r->get('/configuracion',              [SettingsController::class, 'index'],    [SuperAdminMiddleware::class]);
    $r->post('/configuracion',             [SettingsController::class, 'update'],   [SuperAdminMiddleware::class]);
    $r->post('/configuracion/email-prueba',[SettingsController::class, 'testEmail'],[SuperAdminMiddleware::class]);
    $r->get('/configuracion/audit-log',    [SettingsController::class, 'auditLog'], [SuperAdminMiddleware::class]);
});
