# 🚀 INSTRUCCIONES DE DEPLOY — CORRECCIONES URGENTES

## Ubicación en Don Web
```
/home/appcde.online/public_html/eventos/
```

---

## ✅ PASO 1: Subir archivos de configuración por FTP

### 1.1 Archivo: `.env` (renombrar de `.env.production`)
- **Local:** `DeployCorregido/.env`
- **Remoto:** `/eventos/.env`
- **Acción:** Sube y sobrescribe el existente

### 1.2 Archivo: `.htaccess` (raíz)
- **Local:** `DeployCorregido/.htaccess`
- **Remoto:** `/eventos/.htaccess`
- **Acción:** Sube y sobrescribe

### 1.3 Archivo: `public/.htaccess`
- **Local:** `DeployCorregido/public/.htaccess`
- **Remoto:** `/eventos/public/.htaccess`
- **Acción:** Sube y sobrescribe

---

## ✅ PASO 2: Crear/Importar la Base de Datos

⚠️ **IMPORTANTE:** Hazlo en este ORDEN exacto

### 2.1 Importar esquema principal
1. Entra a tu panel Don Web → **Bases de Datos MySQL** → phpMyAdmin
2. Selecciona la BD: `appc_parlamentos`
3. Pestaña **Importar**
4. Sube: `DeployCorregido/database/schema.sql`
5. Haz clic en **Importar**
6. Espera a que termine (debería crear ~15 tablas)

### 2.2 Importar migración adicional
1. Pestaña **Importar**
2. Sube: `DeployCorregido/database/migrations/add_visibility_to_events.sql`
3. Haz clic en **Importar**

---

## ✅ PASO 3: Verificar la Base de Datos

Después de importar, abre **phpMyAdmin** y verifica:

1. Base de datos: `appc_parlamentos` ✓
2. Tablas (debe haber ~15-16):
   - users
   - events
   - submissions
   - audit_logs
   - settings
   - etc.

---

## ✅ PASO 4: Testear la aplicación

Después de completar los pasos anteriores:

1. Abre en navegador: `https://appcde.online/eventos/public/`
2. **Debe mostrar la página de login** (sin error 404 ni error de BD)
3. Si ves error de BD → Vuelve al PASO 2

---

## 🔐 CREDENCIALES DE LA BD (ya configuradas en `.env`)

```
DB_HOST=localhost
DB_PORT=3306
DB_NAME=appc_parlamentos
DB_USER=appc_parlamentos
DB_PASS=DbkI7RUBM7ulJlik
```

---

## 🚨 SI SIGUE DANDO ERROR

### Error 404
→ Verifica que el `.htaccess` está correctamente subido a `/eventos/public/.htaccess`

### Error de conexión a BD
→ Verifica que:
  - La BD `appc_parlamentos` existe en Don Web
  - El usuario `appc_parlamentos` tiene permisos GRANT
  - Las tablas se crearon (paso 2)

### Error de credenciales
→ Abre Don Web → Bases de Datos → Verifica que `appc_parlamentos` user existe con esa contraseña

---

## 📋 CHECKLIST FINAL

- [ ] `.env` subido a `/eventos/`
- [ ] `.htaccess` (raíz) subido a `/eventos/`
- [ ] `public/.htaccess` subido a `/eventos/public/`
- [ ] BD `appc_parlamentos` creada
- [ ] `schema.sql` importado (15+ tablas)
- [ ] `add_visibility_to_events.sql` importado
- [ ] `https://appcde.online/eventos/public/` muestra login (sin errores)

---

**Fecha:** 2026-04-09
**Sistema:** Sistema de Inscripciones — Parlamentos
