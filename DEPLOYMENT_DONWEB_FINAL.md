# 🚀 DEPLOYMENT A DON WEB — GUÍA COMPLETA

**Fecha:** 2026-04-09  
**Sistema:** Sistema de Inscripciones — Parlamentos  
**Dominio:** https://appcde.online/eventos/

---

## 📋 CHECKLIST PRE-DEPLOYMENT

- [ ] Tienes acceso FTP a Don Web (usuario/contraseña)
- [ ] Tienes acceso al panel Don Web (usuario/contraseña)
- [ ] La carpeta `/eventos/` existe en el servidor
- [ ] La BD `appc_parlamentos` existe en Don Web
- [ ] El usuario `appc_parlamentos` existe en Don Web

---

## 🔑 CREDENCIALES (Guarda en lugar seguro)

```
📧 Gmail para correos:
   Email: datos.cdeldorado@gmail.com
   App Password: kmvl xfcv kjvn ytkv

🗄️ Base de Datos:
   Host: localhost
   BD: appc_parlamentos
   Usuario: appc_parlamentos
   Contraseña: DbkI7RUBM7ulJlik

🌐 Dominio:
   URL: https://appcde.online/eventos/public/
```

---

## ✅ PASO 1: SUBIR ARCHIVOS POR FTP

### Archivos que debes subir:

| Archivo Local | Ruta en Don Web | Acción |
|---|---|---|
| `DeployCorregido/.env` | `/eventos/.env` | ✏️ Editar (ver abajo) |
| `DeployCorregido/.htaccess` | `/eventos/.htaccess` | Sobrescribir |
| `DeployCorregido/public/.htaccess` | `/eventos/public/.htaccess` | Sobrescribir |
| `DeployCorregido/app/` | `/eventos/app/` | Sobrescribir carpeta |
| `DeployCorregido/public/` | `/eventos/public/` | Sobrescribir carpeta |
| `DeployCorregido/config/` | `/eventos/config/` | Sobrescribir carpeta |
| `DeployCorregido/vendor/` | `/eventos/vendor/` | Sobrescribir carpeta |

### Software FTP recomendado:
- **Windows/Mac:** [FileZilla](https://filezilla-project.org/) (gratuito)
- **Web:** Administrador de archivos del panel Don Web

---

## ✏️ PASO 2: CONFIGURAR EL `.env` EN DON WEB

⚠️ **IMPORTANTE:** El archivo `.env` debe estar correctamente configurado:

### 2.1 Abre `/eventos/.env` en FTP
Descárgalo, edítalo localmente y vuelve a subir.

### 2.2 Verifica/Actualiza estas líneas:

```ini
# Entorno
APP_ENV=production
APP_DEBUG=false
APP_URL=https://appcde.online/eventos/public

# Base de Datos (Don Web)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=appc_parlamentos
DB_USER=appc_parlamentos
DB_PASS=DbkI7RUBM7ulJlik

# Email (Gmail - YA CONFIGURADO)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=datos.cdeldorado@gmail.com
MAIL_PASSWORD=kmvlxfcvkjvnytkv
MAIL_FROM_ADDRESS=datos.cdeldorado@gmail.com
MAIL_FROM_NAME="Sistema de Inscripciones"
```

---

## 🗄️ PASO 3: CREAR/RESTAURAR LA BASE DE DATOS

### 3.1 Entra al panel Don Web
1. Abre panel.donweb.com
2. Busca: **Hosting** → **Bases de Datos MySQL**
3. Verifica que existe `appc_parlamentos`

### 3.2 Abre phpMyAdmin
1. Haz clic en "phpMyAdmin" para `appc_parlamentos`
2. Se abrirá en una nueva pestaña

### 3.3 Importa el esquema (PASO CRÍTICO)
1. **Pestaña:** "Importar"
2. **Archivo:** Selecciona `DeployCorregido/database/schema_appc_parlamentos.sql`
   - (Si no existe, usa `DeployCorregido/database/schema.sql` pero verifica que use `appc_parlamentos`)
3. **Botón:** Presiona "Importar"
4. **Espera** a que termine (puede tardar 30-60 segundos)

### 3.4 Importa la migración adicional
1. **Pestaña:** "Importar"
2. **Archivo:** `DeployCorregido/database/migrations/add_visibility_to_events.sql`
3. **Botón:** Presiona "Importar"

### 3.5 Verifica las tablas
1. En el panel izquierdo de phpMyAdmin, expande `appc_parlamentos`
2. Debe tener estas tablas:
   - ✓ users
   - ✓ events
   - ✓ forms
   - ✓ submissions
   - ✓ mail_queue
   - ✓ audit_logs
   - ✓ settings
   - (y más...)

Si faltan tablas → **VUELVE AL PASO 3.3**

---

## ✅ PASO 4: SUBIR ARCHIVOS DEL PROYECTO

### 4.1 Conecta por FTP
- **Host:** appcde.online (o tu FTP dado por Don Web)
- **Usuario:** Tu usuario FTP
- **Contraseña:** Tu contraseña FTP
- **Carpeta:** Navega a `/public_html/eventos/`

### 4.2 Sube carpetas (importante: SOBRESCRIBE todo)
```
- app/ → Sube
- config/ → Sube
- database/ → Sube
- public/ → Sube (cuidado con public/.htaccess)
- vendor/ → Sube
- .htaccess → Sube
- .env → Sube (VERIFICADO EN PASO 2)
```

### 4.3 Permisos (si es necesario)
En algunas casos, necesitas ajustar permisos:
- `/eventos/public/uploads/` → 755
- `/eventos/storage/` → 755 (si existe)

---

## 🧪 PASO 5: VERIFICAR QUE FUNCIONA

### 5.1 Abre en navegador
```
https://appcde.online/eventos/public/
```

### 5.2 Deberías ver:
- ✅ Página de login (sin errores 404)
- ✅ Formulario de acceso
- ✅ Sin errores de conexión a BD

### 5.3 Si hay error:

**Error 404 o página en blanco:**
→ Verifica `.htaccess` en `/eventos/public/.htaccess`

**Error de BD (500):**
→ Verifica en paso 3 que las tablas se crearon

**Error "Access denied for user":**
→ Verifica credenciales en `.env` y en Don Web panel

---

## 📧 PASO 6: CONFIGURAR CRON PARA ENVÍO DE CORREOS

⚠️ **SIN ESTE PASO, LOS CORREOS NO SE ENVIARÁN AUTOMÁTICAMENTE**

### 6.1 Entra al panel Don Web
1. **Hosting** → **Tareas Programadas** (o **Cron Jobs**)

### 6.2 Crea una nueva tarea:
- **Descripción:** Procesar cola de correos
- **Comando:**
  ```
  /usr/bin/php /home/appcde.online/public_html/eventos/process-queue.php >> /tmp/mail-queue.log 2>&1
  ```
- **Frecuencia:** Cada 5 minutos (`*/5 * * * *`)

### 6.3 Guarda y verifica
La tarea debe ejecutarse cada 5 minutos automáticamente.

---

## 🧪 PASO 7: PROBAR EL SISTEMA COMPLETAMENTE

### 7.1 Login
1. Abre: `https://appcde.online/eventos/public/admin/login`
2. Usuario: `admin@sistema.com`
3. Contraseña: `Admin@2025!` (deberá cambiar en primer login)

### 7.2 Crear un usuario de prueba
1. Crea un usuario con tu correo de prueba
2. Debería:
   - ✅ Mostrar mensaje de usuario creado
   - ✅ Encolar correo en BD (tabla `mail_queue`)
   - ✅ En 5-10 minutos: el CRON procesa y envía el correo

### 7.3 Verificar correo
- Revisa tu bandeja de entrada
- Debe llegar un correo con la contraseña temporal
- Si no llega en 15 minutos:
  - Verifica el CRON está ejecutándose
  - Revisa tabla `mail_queue` en phpMyAdmin

---

## 🚨 TROUBLESHOOTING

### "Error de conexión a base de datos"
```
Solución:
1. Verifica que appc_parlamentos existe en Don Web
2. Verifica que el usuario appc_parlamentos existe
3. Revisa .env tiene credenciales correctas
4. Intenta conectar directo en phpMyAdmin
```

### "No llegan correos"
```
Solución:
1. Verifica que CRON está configurado (Paso 6)
2. Verifica que MAIL_* están configurados en .env
3. Ejecuta manualmente (SSH):
   /usr/bin/php /home/appcde.online/public_html/eventos/process-queue.php
4. Verifica tabla mail_queue en phpMyAdmin
```

### "Error 404 en formularios"
```
Solución:
1. Verifica .htaccess está en /eventos/public/.htaccess
2. Verifica APP_URL en .env es correcto
3. Reinicia Apache (panel Don Web → Reiniciar)
```

---

## ✅ CHECKLIST FINAL DE DEPLOYMENT

```
📦 Subida de archivos:
- [ ] .env subido y verificado
- [ ] .htaccess (raíz) subido
- [ ] public/.htaccess subido
- [ ] app/ subido
- [ ] config/ subido
- [ ] vendor/ subido
- [ ] database/ subido

🗄️ Base de Datos:
- [ ] BD appc_parlamentos existe
- [ ] schema.sql importado (~15 tablas)
- [ ] add_visibility_to_events.sql importado
- [ ] Tablas visibles en phpMyAdmin

🌐 Funcionamiento:
- [ ] https://appcde.online/eventos/public/ carga (sin 404)
- [ ] Login funciona (admin@sistema.com)
- [ ] Puedo crear usuarios
- [ ] Correos se encolan en mail_queue

📧 Correos:
- [ ] CRON configurado (Paso 6)
- [ ] Correos llegan en 5-10 minutos
- [ ] Gmail está funcionando correctamente
```

---

## 📞 SOPORTE

Si algo falla:
1. Verifica el CHECKLIST final
2. Revisa TROUBLESHOOTING arriba
3. Revisa logs en `/tmp/mail-queue.log` (si existe)
4. Verifica tabla `audit_logs` en BD para errores

**¡Éxito en tu deployment! 🚀**
