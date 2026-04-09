# 🚀 Checklist de Migración a Don Web

Usa este checklist para asegurar que todo está listo antes y después del deploy.

---

## ✓ Antes de Empezar (EN TU MÁQUINA)

### Preparación Local

- [ ] Clonar/tener el proyecto en `/Applications/XAMPP/xamppfiles/htdocs/parlamentos`
- [ ] Tienes acceso al archivo `.env` actual
- [ ] Base de datos local funcionando: `inscripciones_db`
- [ ] XAMPP/Apache corriendo y accesible en `http://localhost/parlamentos`

### Documentos Listos

- [ ] Leer `DEPLOYMENT_GUIDE.md`
- [ ] Leer `DEPLOY_STEPS_DONWEB.md`
- [ ] Este checklist impreso o a mano

### Ejecutar Script de Preparación

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/parlamentos
bash deploy-prepare.sh
```

- [ ] Script ejecutado sin errores
- [ ] Carpeta `deploy-files/` creada
- [ ] Carpeta `backups/` creada
- [ ] Base de datos exportada: `backups/inscripciones_db_*.sql`

### Archivos de Configuración

- [ ] `.env.production` existe
- [ ] `deploy-prepare.sh` ejecutado correctamente
- [ ] `vendor/` existe y tiene contenido
- [ ] `composer.lock` existe

---

## ✓ En Panel Don Web

### Credenciales y Acceso

- [ ] Tienes acceso al panel de control de Don Web
- [ ] Tienes credenciales FTP/SFTP
- [ ] Tienes email y contraseña de acceso

### Base de Datos

- [ ] BD creada en Don Web con charset UTF8MB4
  - Nombre: ___________________
  - Usuario BD: ________________
  - Contraseña: _________________
  - Host: _____________________

- [ ] Usuario MySQL creado y asignado a la BD
- [ ] phpMyAdmin accesible
- [ ] Permisos otorgados (SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER)

### Dominio/Hosting

- [ ] Dominio/Subdominio apuntando a `/eventos` o `/eventos/public/`
- [ ] DNS propagado (puede tomar hasta 24 horas)
- [ ] SSL/HTTPS configurado (Let's Encrypt gratuito)
- [ ] Acceso FTP/SFTP funcionando

### Verificaciones PHP

- [ ] Don Web tiene PHP 7.4+ (contactar si no)
- [ ] Módulo `php-mysql` o `php-pdo_mysql` habilitado
- [ ] Módulo `php-dom` habilitado (para PDF)
- [ ] Módulo Apache `mod_rewrite` habilitado (para .htaccess)

---

## ✓ Antes de Subir (ARCHIVO `.env`)

### Actualizar credenciales en `.env`

En carpeta local `deploy-files/`:

```bash
# Editar: deploy-files/.env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu_dominio/eventos/public

# Base de datos (datos de Don Web)
DB_HOST=localhost
DB_NAME=nombre_de_bd_donweb
DB_USER=usuario_donweb
DB_PASS=contraseña_donweb

# Email (Gmail o servidor)
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_FROM_ADDRESS=noreply@tudominio.com
```

- [ ] `APP_URL` contiene `https` (no `http`)
- [ ] `APP_ENV=production` (no `local`)
- [ ] `APP_DEBUG=false` (NUNCA `true` en producción)
- [ ] Credenciales BD correctas
- [ ] `APP_KEY` es una clave aleatoria (mínimo 32 caracteres)
- [ ] Email configurado correctamente

---

## ✓ Subida de Archivos (FTP)

### Estructura Correcta

Verificar que subiste TODO en la carpeta `/eventos`:

```
/eventos/
├── app/
├── config/
├── routes/
├── public/
│   ├── uploads/
│   ├── .htaccess
│   └── index.php
├── vendor/                 (opcional pero recomendado)
├── composer.json
├── composer.lock
├── .env
└── .htaccess              (raíz del proyecto)
```

- [ ] Carpeta `app/` subida completamente
- [ ] Carpeta `config/` subida completamente
- [ ] Carpeta `routes/` subida completamente
- [ ] Carpeta `public/` con `index.php`
- [ ] `.htaccess` en raíz de `/eventos`
- [ ] `.htaccess` en `/eventos/public/`
- [ ] `.env` subido (renombrado de `.env.production`)
- [ ] Archivo `composer.json` subido
- [ ] Archivo `composer.lock` subido
- [ ] Carpeta `vendor/` subida (opcional pero recomendado)

### NO Subir

- [ ] ~~`.DS_Store`~~ (archivos macOS)
- [ ] ~~`.env.example`~~ (no es necesario)
- [ ] ~~`.git/`~~ (opcional, usa git en servidor si lo necesitas)
- [ ] ~~Archivo `.env` original~~ (solo subir como `.env`)
- [ ] ~~`storage/`~~ (se crea automáticamente)

---

## ✓ Importar Base de Datos

### Importar SQL

- [ ] Archivo backup: `deploy-files/inscripciones_db_*.sql` descargado
- [ ] Accediste a phpMyAdmin del servidor Don Web
- [ ] Seleccionaste la BD creada
- [ ] Importaste el archivo SQL
- [ ] Tablas visibles en phpMyAdmin:
  - [ ] `users`
  - [ ] `events`
  - [ ] `forms`
  - [ ] `submissions`
  - [ ] `audit_logs`
  - [ ] Otras tablas

---

## ✓ Después de Subir (CONFIGURACIÓN EN SERVIDOR)

### Permisos de Archivos

Ejecutar en servidor (SSH):

```bash
chmod 755 app config routes database public
find app -type f -exec chmod 644 {} \;
chmod 775 public/uploads
chmod 775 public/uploads/*
chmod 600 .env
```

- [ ] Permisos configurados correctamente
- [ ] `.env` es privado (`600`)
- [ ] Carpeta `uploads/` es escribible (`775`)

### Crear Carpetas

```bash
mkdir -p public/uploads/events
mkdir -p public/uploads/submissions
mkdir -p public/uploads/system
mkdir -p storage/logs
```

- [ ] Carpetas de uploads existen
- [ ] Carpeta de logs existe

### Verificar Sistema

Abrir navegador:
```
https://tu_dominio/eventos/public
```

- [ ] Página carga sin errores 500
- [ ] Ves página de login o página pública
- [ ] No hay mensajes de error en la página

---

## ✓ Testing Post-Deploy

### Acceso Inicial

Credenciales por defecto:
- Email: `admin@sistema.com`
- Contraseña: `Admin@2025!`

- [ ] Puedes login correctamente
- [ ] Ves el dashboard
- [ ] Sin errores en la consola

### Test de Base de Datos

- [ ] Puedes listar eventos (Admin → Eventos)
- [ ] Puedes listar formularios
- [ ] Puedes ver inscripciones
- [ ] Sin errores 500

### Test de Uploads

- [ ] Crear un evento
- [ ] Subir una imagen de portada
- [ ] Imagen visible en el evento
- [ ] Archivo en `public/uploads/events/`

### Test de Rutas

- [ ] Página pública funciona: `/eventos/public`
- [ ] Evento específico accesible: `/eventos/public/evento/[slug]`
- [ ] Sin errores 404

### Test de Email (Opcional)

- [ ] Configuraste SMTP en `.env`
- [ ] Enviaste email de prueba
- [ ] Email llega a bandeja

---

## ✓ Post-Deploy Seguridad

### Cambiar Contraseña Admin

- [ ] Login con `admin@sistema.com`
- [ ] Ir a Perfil → Cambiar Contraseña
- [ ] Nueva contraseña fuerte (mín. 12 caracteres)
- [ ] Guardar

### Revisar Logs

Conectar por SSH:

```bash
tail -50 storage/logs/errors.log
```

- [ ] No hay errores graves
- [ ] BD conexión OK
- [ ] Permisos OK

### Backups Automáticos

- [ ] Programar backup automático de BD (si Don Web lo ofrece)
- [ ] Backup local de la BD: `backups/inscripciones_db_backup.sql`

---

## ✓ Mantenimiento Continuo

### Actualizaciones

- [ ] Revisar actualizaciones de PHP (Don Web)
- [ ] Actualizar Composer dependencies (si necesario)

### Monitoreo

- [ ] Revisar logs semanalmente: `storage/logs/errors.log`
- [ ] Chequear BD tamaño y performance
- [ ] Monitorear uso de disco (Don Web)

### Documentación

- [ ] Documentar credenciales en lugar seguro
- [ ] Mantener backup local actualizado
- [ ] Guardar guías de deploy para referencia futura

---

## 🎉 Resumen Final

Si completaste TODO en este checklist:

✅ **Tu sistema está completamente migrado y operacional en Don Web**

### Próximos pasos:

1. Comunicar a usuarios la nueva URL
2. Probar acceso desde diferentes navegadores/dispositivos
3. Implementar monitoreo de uptime
4. Planear strategy de backups

---

## 📞 Soporte

Si hay algún problema:

1. **Revisar error en logs:**
   ```bash
   tail -100 storage/logs/errors.log
   ```

2. **Contactar Don Web:**
   - Panel → Soporte → Abrir Ticket
   - Incluir: error específico, PHP version, módulos PHP

3. **Revisar documentación:**
   - `DEPLOYMENT_GUIDE.md`
   - `DEPLOY_STEPS_DONWEB.md`

---

**Última actualización:** 2026-04-09
