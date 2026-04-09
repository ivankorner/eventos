# 🚀 Migración a Don Web — Sistema de Inscripciones

Aquí está todo lo necesario para migrar tu sistema de parlamentos desde XAMPP a un servidor compartido de Don Web.

---

## 📚 Archivos de Esta Carpeta

| Archivo | Descripción |
|---------|-------------|
| **DEPLOY_README.md** | Este archivo (inicio aquí) |
| **DEPLOYMENT_GUIDE.md** | 📖 Guía completa y técnica de migración |
| **DEPLOY_STEPS_DONWEB.md** | 📋 Pasos detallados paso a paso (recomendado) |
| **DEPLOY_CHECKLIST.md** | ✅ Checklist para no olvidar nada |
| **.env.production** | ⚙️ Template de configuración para producción |
| **deploy-prepare.sh** | 🔧 Script que prepara todo automáticamente |

---

## ⚡ Inicio Rápido (3 Pasos)

### 1️⃣ Ejecutar Script de Preparación

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/parlamentos
bash deploy-prepare.sh
```

Esto hará automáticamente:
- ✅ Exportar base de datos
- ✅ Instalar dependencias (`vendor/`)
- ✅ Preparar archivos de configuración
- ✅ Crear carpeta `deploy-files/` lista para FTP

### 2️⃣ Editar Configuración

```bash
# Editar con tus credenciales de Don Web
nano deploy-files/.env
```

Reemplazar:
- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` → Credenciales de Don Web
- `APP_URL` → Tu dominio (ej: `https://tudominio.com/eventos/public`)

### 3️⃣ Subir a Don Web

- Conectar vía FTP a carpeta `/eventos`
- Subir contenido de `deploy-files/` (incluir `vendor/`)
- Importar BD desde `deploy-files/inscripciones_db_*.sql`

**¡Listo!** Tu sistema está en producción.

---

## 📖 Documentación Completa

### Para Entender Todo

👉 **Lee primero:** `DEPLOYMENT_GUIDE.md`
- Explicación detallada de la migración
- Estructura del proyecto
- Configuración de servidor

### Para Hacer el Deploy

👉 **Sigue paso a paso:** `DEPLOY_STEPS_DONWEB.md`
- Instrucciones detalladas por fase
- Screenshots (conceptualmente)
- Troubleshooting

### Para No Olvidar Nada

👉 **Usa como referencia:** `DEPLOY_CHECKLIST.md`
- Antes de empezar
- Durante la migración
- Después del deploy

---

## 🗂️ Estructura de Carpetas

### Local (En tu máquina)

```
/Applications/XAMPP/xamppfiles/htdocs/parlamentos/
├── app/                    ← Código de la aplicación
├── config/                 ← Configuración
├── public/                 ← Archivos públicos
├── vendor/                 ← Dependencias (Composer)
├── database/               ← Scripts SQL
├── composer.json
├── .env                    ← Variables locales
├── .env.production         ← Template para producción
├── DEPLOYMENT_GUIDE.md     ← 📖 Lee primero esto
├── DEPLOY_STEPS_DONWEB.md  ← 📋 Sigue estos pasos
├── DEPLOY_CHECKLIST.md     ← ✅ Checklist
└── deploy-prepare.sh       ← Script de prep
```

### En servidor Don Web (Después del deploy)

```
/home/usuario/public_html/eventos/
├── app/
├── config/
├── public/
│   └── uploads/
├── vendor/
├── composer.json
├── .env                    ← Credenciales reales
└── .htaccess
```

---

## 🔑 Credenciales Importantes

### Para Local (XAMPP)

```
Database:
  Host: localhost
  User: root
  Pass: (vacío)
  Name: inscripciones_db
URL: http://localhost/parlamentos/public
```

### Para Don Web (Cambiar después)

```
Database:
  Host: localhost (proporcionado por Don Web)
  User: usuario_donweb (crear en panel)
  Pass: contraseña_segura (crear en panel)
  Name: nombre_donweb (crear en panel)
URL: https://tu_dominio.com/eventos/public
```

---

## ⚠️ Puntos Críticos

Estas cosas **NO debes olvidar:**

1. **Cambiar contraseña admin:**
   - Default: `admin@sistema.com` / `Admin@2025!`
   - ⚠️ Cambiar en primer login

2. **Configurar `.env` correctamente:**
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - Credenciales BD reales
   - `APP_URL` con HTTPS

3. **Permisos en servidor:**
   - `chmod 600 .env` (privado)
   - `chmod 775 public/uploads` (escribible)

4. **Base de datos:**
   - Crear en panel Don Web
   - Importar `inscripciones_db_*.sql`
   - Verificar tablas en phpMyAdmin

5. **Dominio:**
   - Apuntar a `/eventos/public/`
   - Esperar propagación DNS
   - Verificar HTTPS

---

## 🐛 Si Algo Falla

### Error 500

1. Revisa `.env` (credenciales BD)
2. Mira `storage/logs/errors.log`
3. Verifica BD existe y está importada
4. Contacta Don Web (módulos PHP)

### Error 404

1. Verifica `.htaccess` existe
2. Don Web debe tener `mod_rewrite` habilitado
3. Contacta Don Web

### Uploads no funcionan

1. Verifica `public/uploads` tiene permisos 775
2. Crea carpetas manualmente si no existen

### Email no funciona

1. Verifica SMTP en `.env`
2. Gmail requiere "contraseña de aplicación"
3. Prueba con otro email si es posible

---

## 📞 Recursos

### Documentación del Proyecto

- `DEPLOYMENT_GUIDE.md` — Guía técnica completa
- `DEPLOY_STEPS_DONWEB.md` — Pasos detallados
- `DEPLOY_CHECKLIST.md` — Verificaciones

### Ayuda Externa

- **Don Web Support:** Panel → Soporte
- **PHP Info:** `php -v`, `php -m`
- **MySQL Test:** `mysql -u usuario -p base_datos`

---

## ✅ Verificación Rápida

Después de hacer deploy, verifica:

```bash
# En servidor (SSH)
cat /home/usuario/public_html/eventos/.env
# Debería mostrar credenciales reales

# Verificar BD conexión
mysql -h localhost -u usuario -p nombre_db -e "SELECT COUNT(*) FROM users;"
# Debería retornar el número de usuarios

# Ver logs
tail -20 /home/usuario/public_html/eventos/storage/logs/errors.log
# No debería haber errores graves
```

---

## 🎯 Próximos Pasos

1. **Leer:** `DEPLOYMENT_GUIDE.md`
2. **Ejecutar:** `bash deploy-prepare.sh`
3. **Seguir:** `DEPLOY_STEPS_DONWEB.md`
4. **Verificar:** `DEPLOY_CHECKLIST.md`
5. **Disfrutar:** Sistema en producción ✨

---

## 📝 Versión

- **Proyecto:** Sistema de Inscripciones (Parlamentos)
- **Versión:** 1.0
- **Servidor Target:** Don Web (Shared Hosting)
- **Fecha:** 2026-04-09
- **Autor:** Guía de Migración Automated

---

**¿Preguntas?** Revisa las guías detalladas o contacta a Don Web.

**¡Mucho éxito con tu deploy! 🚀**
