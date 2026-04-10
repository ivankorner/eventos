# PROJECT_MEMORY.md — Memoria acumulada del proyecto

> Leer este archivo al inicio de cada sesión. Actualizarlo cuando se resuelva algo no obvio.

---

## Configuración de entorno

- **Local**: XAMPP, BD `inscripciones_db`, URL `http://localhost/parlamentos/public`
- **Producción**: DonWeb, BD `appc_eventos`, usuario `appc_eventos`
- **SMTP**: `no-reply@appcde.online` — ver credenciales en memory persistente de Claude

---

## Decisiones de arquitectura

- No se usa ningún framework PHP — todo es código propio (router, middleware, vistas)
- Los emails tienen fallback: si falla el envío, la inscripción igual se guarda
- El sistema de colas de email existe pero es opcional (process-queue.php)

---

## Bugs resueltos

| Fecha | Bug | Solución |
|-------|-----|----------|
| 2026-04 | Emails de inscripción bloqueaban el registro | Se implementó fallback: inscripción no depende del email |
| 2026-04 | Dropdown "Acciones" en lista de eventos no funcionaba | Fix en JS del template de eventos |

---

## Notas de deploy

- El deploy va a `/DeployCorregido/` o `/deploy-files/`
- Siempre copiar `.env` manualmente al servidor (no está en git)
- Archivos de diagnóstico (`diagnose-emails.php`, `test-smtp.php`) no deben quedar en producción

---

## Pendientes / ideas

<!-- Agregar aquí tareas futuras o ideas sin implementar -->
