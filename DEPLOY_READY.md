# ✅ DEPLOY LISTO — Instrucciones Finales

Tu servidor Don Web está configurado y listo. Aquí está lo que necesitas hacer:

---

## 📋 Credenciales de Don Web (Confirmadas)

```
Base de Datos:
  Name: appc_eventos
  User: appc_eventos
  Pass: $A8sA4!24vFAIXVs
```

✅ **Verificado:** Estas credenciales ya están en los archivos `.env.donweb` y `.env.production`

---

## 🚀 Pasos Finales (Rápido)

### 1. Preparar en tu máquina (XAMPP)

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/parlamentos

# Ejecutar script de preparación
bash deploy-prepare.sh
```

Esto crea:
- ✅ `deploy-files/` con todos los archivos listos
- ✅ `backups/inscripciones_db_*.sql` (backup de tu BD local)
- ✅ `vendor/` con todas las dependencias

**Duración:** ~2-5 minutos dependiendo de tu conexión

### 2. Editar configuración

El archivo `.env.donweb` ya tiene las credenciales reales. Solo debes agregar:

```bash
# Editar uno de estos archivos:
# - .env.donweb (recomendado, ya tiene credenciales)
# - deploy-files/.env (si lo prefieres)

# Cambiar SOLO estos valores:
APP_URL=https://tu_dominio_real.com/eventos/public
APP_KEY=tu_clave_generada_con_openssl_rand_hex_32
MAIL_USERNAME=tu_email@gmail.com
MAIL_PASSWORD=tu_app_password
MAIL_FROM_ADDRESS=noreply@tu_dominio.com
```

### 3. Copiar archivo .env a carpeta deploy

```bash
# Copiar el archivo con credenciales a la carpeta que vas a subir
cp .env.donweb deploy-files/.env
```

### 4. Subir vía FTP a Don Web

**Ruta de destino:** `/eventos`

**Archivos a subir:**
```
deploy-files/
├── app/                    ← Código
├── config/                 ← Configuración
├── routes/                 ← Rutas
├── public/                 ← Archivos públicos
├── vendor/                 ← Dependencias (PESADO)
├── database/               ← Scripts SQL
├── .env                    ← Configuración (renombrado de .env.donweb)
├── .htaccess               ← Reescritura de URLs
├── composer.json
└── composer.lock
```

**Importante:**
- Usar un cliente FTP como **Filezilla** o **WinSCP**
- Cambiar `deploy-files/.env` a solo `.env` en el servidor

### 5. Importar Base de Datos

En panel Don Web:

1. Ir a **phpMyAdmin** → **appc_eventos**
2. Pestaña **Importar**
3. Subir: `deploy-files/inscripciones_db_*.sql`
4. Click **Ejecutar**

Debería ver ✅ tablas creadas:
- users
- events
- forms
- submissions
- audit_logs
- y otras

### 6. Configurar Permisos en Servidor

Vía SSH (si disponible):

```bash
cd /home/usuario/public_html/eventos

# Permisos
chmod 755 app config routes database public
find app -type f -exec chmod 644 {} \;
chmod 775 public/uploads
chmod 775 public/uploads/*
chmod 600 .env
```

---

## 🔗 Dominio

¿Cuál es tu dominio en Don Web?

```
Debería apuntar a:
https://tu_dominio.com/eventos/public/
```

- [ ] ¿Ya tienes el dominio configurado en Don Web?
- [ ] ¿Apunta a la carpeta `/eventos/public/`?
- [ ] ¿DNS propagado?

---

## ✅ Checklist Final

Antes de decir "listo":

- [ ] Script `bash deploy-prepare.sh` ejecutado
- [ ] Carpeta `deploy-files/` creada con contenido
- [ ] Archivo `.env.donweb` editado con tu dominio
- [ ] Copiar `.env.donweb` como `deploy-files/.env`
- [ ] Todos los archivos subidos vía FTP a `/eventos`
- [ ] BD `appc_eventos` creada en Don Web
- [ ] SQL importado desde `deploy-files/inscripciones_db_*.sql`
- [ ] Permisos configurados (chmod)
- [ ] Acceso a `https://tu_dominio.com/eventos/public` funciona
- [ ] Login con `admin@sistema.com` / `Admin@2025!` ✅
- [ ] Dashboard carga sin errores
- [ ] Cambiar contraseña admin inmediatamente

---

## 📁 Archivos Importantes

En tu carpeta local tendrás después del script:

```
/Applications/XAMPP/xamppfiles/htdocs/parlamentos/
├── deploy-files/                  ← TODO LISTO PARA SUBIR
│   ├── app/
│   ├── config/
│   ├── public/
│   ├── vendor/
│   ├── .env                       ← EDITAR CON TU DOMINIO
│   ├── inscripciones_db_*.sql     ← IMPORTAR EN BD
│   └── CREDENCIALES_DONWEB.txt
│
├── backups/
│   └── inscripciones_db_*.sql     ← BACKUP LOCAL
│
└── .env.donweb                    ← Template con credenciales reales
```

---

## 🆘 Si Algo Falla

### Error 500 en la web

**Revisar:**
1. `.env` tiene credenciales correctas:
   - DB_NAME: `appc_eventos`
   - DB_USER: `appc_eventos`
   - DB_PASS: `$A8sA4!24vFAIXVs`

2. BD importada correctamente (phpMyAdmin)

3. Logs de error:
   ```bash
   tail -100 storage/logs/errors.log
   ```

### Error 404 en rutas

**Problema:** `.htaccess` no funciona
**Solución:** Contactar Don Web para activar `mod_rewrite` en Apache

### No puedo conectar vía FTP

**Revisar:**
1. Credenciales FTP correctas (diferentes a BD)
2. Puerto 21 (FTP) o 22 (SFTP)
3. Firewall no bloquea conexión

---

## 📞 Contacto Don Web

Si necesitas soporte:

**Panel Don Web → Soporte → Abrir Ticket**

Menciona:
- Error específico (500, 404, etc)
- Archivo `storage/logs/errors.log` (si existe)
- `php -v` (versión PHP)
- `php -m` (módulos habilitados)

---

## 🎉 Éxito

Una vez que accedas a:
```
https://tu_dominio.com/eventos/public
```

Y veas el login o la página pública, **¡es que funciona!**

---

## 📝 Pasos Próximos (Post-Deploy)

1. **Login:** `admin@sistema.com` / `Admin@2025!`
2. **Cambiar contraseña admin** (OBLIGATORIO)
3. **Configurar SMTP** si quieres emails
4. **Crear eventos** de prueba
5. **Compartir URL** con usuarios

---

## 📖 Documentación

Si necesitas más detalles:

- `DEPLOYMENT_GUIDE.md` — Todo técnico
- `DEPLOY_STEPS_DONWEB.md` — Paso a paso
- `DEPLOY_CHECKLIST.md` — Verificaciones
- `DEPLOY_README.md` — Visión general

---

**¿Algo en duda?** Revisa las guías detalladas o pregunta a Don Web.

**¡Mucho éxito! 🚀**
