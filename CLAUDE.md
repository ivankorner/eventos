# CLAUDE.md — Sistema de Inscripciones

## Proyecto
Aplicación PHP MVC para gestión de eventos e inscripciones públicas. Sin framework externo.

## Stack
- PHP 8+ (sin framework), Router propio, Vistas PHP nativas
- MySQL via XAMPP local / DonWeb en producción
- SMTP para emails (no-reply@appcde.online)
- Composer para dependencias

## Estructura clave
```
public/          → entry point (index.php)
routes/web.php   → todas las rutas
app/
  Controllers/   → lógica HTTP
  Models/        → acceso a BD
  Views/         → HTML + PHP
  Helpers/       → Email, etc.
config/          → configuración
.env             → variables de entorno (nunca commitear)
```

## Convenciones
- Rutas en `routes/web.php`
- Modelos extienden `BaseModel`
- Variables de entorno vía `$_ENV` o helper `env()`
- Admin protegido por `AuthMiddleware`

## Memoria del proyecto
Ver [PROJECT_MEMORY.md](PROJECT_MEMORY.md) — decisiones, bugs resueltos y contexto acumulado.
