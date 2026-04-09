# Guía de Deploy — Sistema de Inscripciones a Don Web

## 📋 Resumen de Migración

Este documento detalla cómo migrar el sistema de inscripciones desde tu máquina local (XAMPP) al servidor compartido de Don Web en la carpeta `/eventos`.

---

## 🏗️ Estructura del Proyecto

```
/eventos/
├── app/                    # Código de la aplicación (MVC)
├── public/                 # Raíz web pública
│   ├── index.php          # Front controller
│   ├── .htaccess          # Reglas de reescritura URL
│   └── uploads/           # Archivos subidos
├── config/                # Configuraciones
├── database/              # Scripts de BD
├── routes/                # Definición de rutas
├── vendor/                # Dependencias Composer (generadas)
├── .env                   # Variables de entorno (IMPORTANTE: NO subir a repo)
├── .htaccess              # Reescritura para shared hosting
├── composer.json          # Dependencias del proyecto
└── composer.lock          # Lock file
```

---

## 📁 Archivos a Preparar

### Antes de Subir

Los siguientes archivos **DEBEN** prepararse según el servidor remoto:

1. **`.env`** — Variables de entorno (crear nuevo, NO copiar el local)
2. **`composer.lock`** — Lock file de dependencias
3. **Carpeta `vendor/`** — Generada al ejecutar `composer install`
4. **Carpeta `public/uploads/`** — Archivos subidos (sincronizar)
5. **Base de datos** — Exportar esquema + datos

---

## 🚀 Pasos de Migración

### **Paso 1: Preparar la Base de Datos**

#### En tu máquina local (XAMPP):

1. **Exportar esquema + datos:**
   ```bash
   # Opción A: Desde línea de comandos (recomendado)
   mysqldump -u root inscripciones_db > database/inscripciones_db_backup.sql
   
   # Opción B: Desde phpMyAdmin
   # - Ir a phpMyAdmin > inscripciones_db > Exportar
   # - Elegir "SQL" como formato
   # - Descargar el archivo
   ```

2. **Verificar el backup:**
   ```bash
   # Ver primeras líneas
   head -20 database/inscripciones_db_backup.sql
   ```

#### En el servidor Don Web:

1. **Crear la base de datos:**
   - Acceder al panel de control de Don Web
   - Ir a "Bases de datos MySQL"
   - Crear una nueva BD con charset `utf8mb4`
   - Anotar: `nombre_bd`, `usuario_bd`, `contraseña_bd`

2. **Importar datos:**
   - Via phpMyAdmin del servidor:
     - Seleccionar la BD
     - Ir a "Importar"
     - Subir `inscripciones_db_backup.sql`
   - O via SSH (si está disponible):
     ```bash
     mysql -u usuario_bd -p nombre_bd < inscripciones_db_backup.sql
     ```

---

### **Paso 2: Preparar el Código**

#### 1. Crear el archivo `.env` para producción:

```bash
# Copiar el template
cp .env .env.example   # (si no existe)
cp .env.example .env.production

# Editar .env.production con datos del servidor
# (Ver sección 3 más abajo)
```

#### 2. Instalar dependencias localmente:

```bash
# En tu máquina, asegurar composer está actualizado
composer install --no-dev --optimize-autoloader

# Esto genera:
# - carpeta vendor/ (la más pesada, pero necesaria)
# - vendor/autoload.php (carga automática de clases)
```

#### 3. Archivos a subir vía FTP:

**Obligatorios:**
- `app/` → Código de la aplicación
- `config/` → Archivos de configuración
- `routes/` → Definición de rutas
- `database/` → Scripts SQL (para referencia)
- `public/` → Archivos públicos, JS, CSS, imágenes
- `vendor/` → Dependencias (PESADO, ~50MB, pero necesario)
- `composer.json` y `composer.lock`
- `.env.production` → Renombrar a `.env`
- `.htaccess` (en raíz) → Importante para shared hosting

**Opcionales (para desarrollo):**
- `.git/` → Si usas versionado en el servidor
- `tests/` → Tests unitarios (no necesarios en producción)

**NUNCA subir:**
- `.DS_Store` (archivos del sistema macOS)
- `storage/` → Se crea automáticamente
- `.env` original (subir renombrado como `.env.production`)

---

### **Paso 3: Configurar `.env` para Don Web**

Crear archivo `.env.production` (adaptar valores según tu servidor):

```env
# ===================================================
# PRODUCCIÓN — Don Web
# ===================================================

# Entorno
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com/eventos/public
APP_NAME="Sistema de Inscripciones"

# Clave secreta (generar: openssl rand -hex 16)
APP_KEY=tu_clave_aleatoria_de_32_caracteres

# Base de datos (datos proporcionados por Don Web)
DB_HOST=localhost            # O la IP del servidor de BD
DB_PORT=3306
DB_NAME=tu_nombre_de_bd
DB_USER=tu_usuario_bd
DB_PASS=tu_contraseña_bd

# Email (SMTP - recomendado Gmail con app password)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_FROM_ADDRESS=noreply@tudominio.com
MAIL_FROM_NAME="Sistema de Inscripciones"

# Seguridad
CSRF_TOKEN_LIFETIME=3600
SESSION_LIFETIME=7200
SESSION_NAME=insc_session

# Archivos
UPLOAD_MAX_SIZE=5242880
UPLOAD_ALLOWED_TYPES=pdf,jpg,jpeg,png,webp
UPLOAD_PATH=../public/uploads

# Rate limiting
RATE_LIMIT_SUBMISSIONS=5
RATE_LIMIT_WINDOW=3600
```

**⚠️ IMPORTANTE:** 
- Cambiar `APP_DEBUG=false` en producción (NUNCA `true`)
- Generar `APP_KEY` nuevo: `openssl rand -hex 32`
- Usar credenciales BD reales de Don Web

---

### **Paso 4: Configurar el Servidor (Don Web)**

#### A. Permisos de archivos:

```bash
# Vía SSH (si está disponible), desde la carpeta /eventos:

# Archivos legibles
find . -type f -exec chmod 644 {} \;

# Directorios ejecutables
find . -type d -exec chmod 755 {} \;

# Especiales: carpeta de uploads
chmod 775 public/uploads/
chmod 775 public/uploads/submissions/
chmod 775 public/uploads/events/
chmod 775 public/uploads/system/

# Archivo .env debe ser privado
chmod 600 .env
```

#### B. Configurar dominio/subdominio:

En el panel de Don Web:
- Apuntar el dominio a `/eventos/public/`
- O crear un alias/subdominio que apunte a `eventos/public/`

#### C. Verificar módulos PHP necesarios:

```bash
# Contactar a Don Web para verificar:
- PHP 7.4+ (mínimo)
- Extensión `php-mysql` o `php-pdo_mysql`
- Extensión `php-dom` (para generación de PDF)
- Módulo `mod_rewrite` para Apache (para .htaccess)
```

---

### **Paso 5: Post-Deploy**

#### 1. Verificar la instalación:

```bash
# Abrir en navegador:
https://tudominio.com/eventos/public/

# Debería mostrar:
# - Página de login
# - O página pública de eventos (si existen)
```

#### 2. Cambiar contraseña de admin:

- Login con: `admin@sistema.com` / `Admin@2025!`
- Ir a Perfil → Cambiar contraseña
- **⚠️ OBLIGATORIO** en primer login

#### 3. Revisar archivos de log:

```bash
# Verificar carpeta de logs (crear si no existe):
storage/logs/

# Revisar errors.log para diagnosticar problemas
tail -f storage/logs/errors.log
```

#### 4. Sincronizar archivos periódicamente:

```bash
# Si hay cambios en local:
rsync -avz --exclude='vendor' --exclude='.git' --exclude='.env' \
  ./ usuario@servidor:/home/usuario/public_html/eventos/
```

---

## 🔧 Troubleshooting

| Problema | Causa | Solución |
|----------|-------|----------|
| **Error 500** | Permisos incorrectos | Verificar permisos (644/755) |
| **Database connection refused** | Credenciales BD incorrectas | Revisar `.env` vs panel Don Web |
| **404 en rutas** | `.htaccess` no funciona | Activar `mod_rewrite` en Apache |
| **Uploads no funcionan** | Permisos `public/uploads/` | Cambiar a `chmod 775` |
| **Composer no carga** | `vendor/autoload.php` falta | Ejecutar `composer install` |
| **HTTPS mixed content** | URL en `.env` es `http` | Cambiar a `https` en `.env` |

---

## 📋 Checklist Pre-Deploy

- [ ] Backup local de BD: `mysqldump -u root inscripciones_db > backup.sql`
- [ ] Revisar `.env.production` con datos correctos
- [ ] Generar nueva `APP_KEY`: `openssl rand -hex 32`
- [ ] Ejecutar `composer install --no-dev --optimize-autoloader`
- [ ] Carpeta `vendor/` existe y tiene archivos
- [ ] Carpeta `public/uploads/` tiene permisos 775
- [ ] Archivo `.env` tiene permisos 600 en servidor
- [ ] BD creada en Don Web y datos importados
- [ ] Dominio apunta a `/eventos/public/`
- [ ] Módulos PHP verificados (mysql, dom, rewrite)

---

## 📞 Contacto y Soporte

Si encuentras problemas:
1. Revisar `storage/logs/errors.log`
2. Contactar a Don Web (verificar módulos habilitados)
3. Verificar configuración `.env` vs BD real

