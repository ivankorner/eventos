# Pasos Detallados: Deploy en Don Web

Guía paso a paso para subir el Sistema de Inscripciones a un servidor compartido de Don Web.

---

## 📋 Antes de Empezar

- [ ] Tienes acceso al panel de control de Don Web
- [ ] Tienes credenciales FTP/SFTP
- [ ] Corriste `bash deploy-prepare.sh` en local
- [ ] Tienes los archivos en la carpeta `deploy-files/`
- [ ] Leíste `DEPLOYMENT_GUIDE.md`

---

## 🔧 FASE 1: Configuración en Panel Don Web

### 1.1 - Crear Base de Datos

**En el panel de Don Web:**

1. Ir a **Hosting** → **Bases de Datos MySQL** (o similar)
2. Click en **Crear Nueva Base de Datos**
3. Completar:
   - **Nombre BD**: `inscripciones_db` (o similar)
   - **Charset**: UTF8MB4
   - **Collation**: utf8mb4_unicode_ci
4. Crear usuario MySQL:
   - **Usuario**: `inscripciones_user` (o similar)
   - **Contraseña**: Una segura (guardar bien)
   - **Host**: `localhost` (típicamente)
5. Asignar todos los permisos al usuario
6. **Guardar**

**Resultado a anotar:**
```
DB_HOST: localhost (o IP proporcionada)
DB_NAME: inscripciones_db
DB_USER: inscripciones_user
DB_PASS: tu_contraseña_segura
```

### 1.2 - Acceder a phpMyAdmin

1. En el panel de Don Web, buscar **phpMyAdmin**
2. Login con credenciales de BD creadas
3. Navegar a la BD `inscripciones_db`
4. Ir a pestaña **Importar**
5. Subir archivo: `deploy-files/inscripciones_db_*.sql`
6. Click en **Ejecutar**

✅ La BD está lista cuando aparecen las tablas.

---

## 📤 FASE 2: Subir Archivos vía FTP

### 2.1 - Conectar vía FTP

**Credenciales que recibes de Don Web:**
```
Host: ftp.tudominio.com (o IP)
Usuario: usuario_ftp
Contraseña: contraseña_ftp
Puerto: 21 (FTP) o 22 (SFTP)
```

**Recomendación:** Usar **Filezilla** (gratuito) o **WinSCP**

### 2.2 - Estructura de carpetas en servidor

```
/home/usuario/public_html/
├── index.php              (redirige a eventos/public)
├── .htaccess              (importante)
└── eventos/               ← TU PROYECTO
    ├── app/
    ├── config/
    ├── routes/
    ├── public/
    ├── vendor/
    ├── composer.json
    ├── .env
    └── .htaccess
```

### 2.3 - Pasos de Upload con Filezilla

1. **Conectar:**
   - Archivo → Gestor de sitios → Nuevo sitio
   - Host: `ftp.tudominio.com`
   - Tipo: FTP
   - Usuario y contraseña
   - Conectar

2. **Navegar a `/eventos`:**
   - En la lista del servidor, buscar `/home/usuario/public_html/eventos`
   - Si no existe, crear carpeta `eventos`

3. **Subir archivos (orden recomendado):**

   **Primero (rápido):**
   ```
   config/              ← Configuración
   routes/              ← Rutas
   database/            ← Scripts SQL (para referencia)
   app/                 ← Código
   public/              ← Estáticos
   .htaccess
   composer.json
   composer.lock
   .env                 ← ÚLTIMO (una vez editado)
   ```

   **Después (pesado, puede tomar tiempo):**
   ```
   vendor/              ← Dependencias (~70 MB)
   ```

   **Verificar**: En el servidor debe haber exactamente estos archivos.

---

## ⚙️ FASE 3: Configuración en el Servidor

### 3.1 - Editar `.env` en servidor

**Opción A: Vía File Manager (panel Don Web)**
1. En el panel, buscar **File Manager** o **Administrador de Archivos**
2. Navegar a `/eventos/.env`
3. Click derecho → Editar
4. Reemplazar valores de BD con credenciales reales:
   ```env
   DB_HOST=localhost
   DB_NAME=inscripciones_db
   DB_USER=inscripciones_user
   DB_PASS=contraseña_real
   APP_URL=https://tudominio.com/eventos/public
   ```
5. Guardar

**Opción B: Vía FTP (Filezilla)**
1. Descargar `.env` desde servidor
2. Editar localmente con editor de texto
3. Subir nuevamente

**Opción C: Vía SSH (si está disponible)**
```bash
ssh usuario@tudominio.com
cd /home/usuario/public_html/eventos
nano .env
# Editar y guardar (Ctrl+X, Y, Enter)
```

### 3.2 - Configurar Permisos

**Vía SSH (recomendado):**
```bash
cd /home/usuario/public_html/eventos

# Permisos estándar
chmod 755 app config routes database public
find app -type f -exec chmod 644 {} \;
find config -type f -exec chmod 644 {} \;
find public -type f -exec chmod 644 {} \;

# Permisos especiales para escritura
chmod 775 public/uploads
chmod 775 public/uploads/events
chmod 775 public/uploads/submissions
chmod 775 public/uploads/system
chmod 755 storage
chmod 755 storage/logs

# .env debe ser privado
chmod 600 .env
chmod 600 .env.production
```

**Vía File Manager (panel):**
1. File Manager → Seleccionar archivo/carpeta
2. Click derecho → Permisos
3. Cambiar según la tabla arriba

### 3.3 - Crear carpetas si no existen

```bash
mkdir -p public/uploads/events
mkdir -p public/uploads/submissions
mkdir -p public/uploads/system
mkdir -p storage/logs
```

---

## 🌐 FASE 4: Configurar Dominio

### 4.1 - Apuntar dominio a `/eventos/public/`

**Opción A: Subdominio (recomendado)**
1. En panel Don Web → Dominios/Subdominio
2. Crear subdominio: `eventos.tudominio.com`
3. Apuntar a: `/home/usuario/public_html/eventos/public`
4. Esperar propagación DNS (~2-24 horas)

**Opción B: Directorio en dominio principal**
1. Carpeta `/` apunta a `/home/usuario/public_html`
2. En `.htaccess` (raíz):
   ```apache
   RewriteEngine On
   RewriteRule ^eventos/(.*)$ eventos/public/$1 [L]
   ```

**Opción C: Carpeta sin reescritura**
1. URL será: `https://tudominio.com/eventos/public`
2. (Funciona pero menos elegante)

### 4.2 - Verificar SSL/HTTPS

- Don Web típicamente ofrece SSL gratuito (Let's Encrypt)
- En `.env` usar HTTPS:
  ```env
  APP_URL=https://tudominio.com/eventos/public
  ```

---

## ✅ FASE 5: Verificaciones Finales

### 5.1 - Pruebas básicas

Abrir en navegador:
```
https://tudominio.com/eventos/public
```

Debería ver:
- [ ] Página de login (si no estás en sesión)
- [ ] O panel principal (si acceso activo)
- [ ] Sin errores 500
- [ ] Página carga rápido

### 5.2 - Verificar conectividad a BD

1. Login como admin:
   - Email: `admin@sistema.com`
   - Pass: `Admin@2025!` (default)

2. Ir a Admin → Dashboard
   - Debe cargar sin errores
   - Si hay error 500: revisar `.env` y BD

### 5.3 - Revisar logs de error

**En servidor vía SSH:**
```bash
# Ver últimos errores
tail -50 storage/logs/errors.log

# Ver en tiempo real
tail -f storage/logs/errors.log
```

**Si archivo no existe:**
```bash
mkdir -p storage/logs
touch storage/logs/errors.log
chmod 666 storage/logs/errors.log
```

### 5.4 - Test de upload

1. Ir a Admin → Crear Evento
2. Subir una imagen de prueba
3. Verificar archivo en: `public/uploads/events/`

---

## 🚨 TROUBLESHOOTING

### Error 500 - Internal Server Error

**1. Revisar `.env`:**
```bash
# Conectar por SSH
cat .env
# Verificar: DB_HOST, DB_NAME, DB_USER, DB_PASS
```

**2. Revisar BD conectividad:**
```bash
# Desde SSH (si está disponible)
mysql -h localhost -u inscripciones_user -p inscripciones_db -e "SELECT 1;"
# Debe retornar 1
```

**3. Revisar logs:**
```bash
tail -100 storage/logs/errors.log
```

**4. Verificar PHP:**
```bash
php -v
# Debe ser >= 7.4
php -m | grep -i pdo
# Debe mostrar pdo y pdo_mysql
```

---

### Error 404 - Rutas no funcionan

**Problema:** `.htaccess` no se ejecuta

**Solución:**
```bash
# Verificar que mod_rewrite está habilitado en Apache
# Contactar a Don Web para verificar

# O verificar .htaccess:
cat public/.htaccess
# Debe tener: RewriteEngine On
```

---

### Error de Permisos - Permission Denied

```bash
# Dar permisos al usuario FTP/web
chmod 777 public/uploads
chmod 777 storage/logs

# O más seguro:
chmod 775 public/uploads
sudo chown www-data:www-data public/uploads
```

---

### Emails no se envían

**Verificar en `.env`:**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
```

**Gmail requiere:**
1. Verificar cuenta
2. Activar "Contraseñas de aplicación"
3. Usar la contraseña de aplicación (NO la contraseña normal)

---

## 📞 Contactar Soporte

Si necesitas ayuda de Don Web:
1. Panel → Soporte → Abrir Ticket
2. Indicar:
   - El error específico
   - Archivo `storage/logs/errors.log` (si existe)
   - Configuración PHP (`php -v`, `php -m`)

---

## ✨ Felicidades

Si llegaste hasta acá, tu sistema está **listo en producción** 🚀

**No olvides:**
- [ ] Cambiar contraseña admin (`Admin@2025!` → Nueva)
- [ ] Configurar email real (SMTP Gmail u otro)
- [ ] Crear backups periódicos
- [ ] Monitorear `storage/logs/errors.log`

