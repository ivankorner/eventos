# 📧 Configuración SMTP - appcde.online

## Resumen de cambios

Se ha **reemplazado Gmail por el servidor SMTP propietario de appcde.online**. La configuración está lista para producción.

---

## ✅ Configuración actualizada

```
MAIL_HOST=appcde.online
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=no-reply@appcde.online
MAIL_PASSWORD=A26_H3{/N]z<
MAIL_FROM_ADDRESS=no-reply@appcde.online
MAIL_FROM_NAME=Eventos CDE
```

Estos valores ya están en el archivo `.env` de esta carpeta.

---

## 🚀 Proceso de Deploy

### Paso 1: Pruebas locales (OPCIONAL pero recomendado)

Si estás en un entorno con PHP CLI disponible:

```bash
# Probar conexión SMTP (sin enviar correos)
php test-smtp-connection.php

# Enviar correo de prueba
php test-send-mail.php tu_email@example.com
```

### Paso 2: Subir archivos a Don Web (FTP)

1. **Conecta con FileZilla u otro cliente FTP:**
   - Host: appcde.online
   - Usuario: tu_usuario_ftp
   - Contraseña: tu_contraseña_ftp

2. **Navega a:** `/public_html/eventos/`

3. **Sube/sobrescribe:**
   - `app/` (directorio completo)
   - `config/` (directorio completo)
   - `.env` (archivo actualizado) ⚠️ **IMPORTANTE**
   - `vendor/` (si no está)
   - `public/` (si no está)

⚠️ **IMPORTANTE:** Asegúrate de sobrescribir el `.env` con los datos actuales

### Paso 3: Verificar en el servidor

En caso de que tengas acceso SSH al servidor:

```bash
# Conectar por SSH
ssh usuario@appcde.online

# Ir a la carpeta
cd /home/appcde.online/public_html/eventos

# Probar conexión SMTP
php test-smtp-connection.php

# Probar envío (reemplaza email)
php test-send-mail.php tu_email@example.com
```

### Paso 4: Configurar CRON (Automático)

1. Accede a **panel.donweb.com**
2. Ve a **Hosting → Tareas Programadas** (o Cron Jobs)
3. **Nueva tarea:**
   - **Descripción:** Procesar cola de correos
   - **Comando:**
     ```
     /usr/bin/php /home/appcde.online/public_html/eventos/process-queue.php >> /tmp/mail-queue.log 2>&1
     ```
   - **Frecuencia:** `*/5 * * * *` (cada 5 minutos)
4. Guardar

### Paso 5: Probar envío automático

1. **En el panel de administración:**
   - Login: https://appcde.online/eventos/public/admin/login
   - Usuario: admin@sistema.com
   - Contraseña: Admin@2025!

2. **Crear un usuario nuevo:**
   - Admin → Usuarios → Crear usuario
   - Completa los datos

3. **Esperar:**
   - El CRON se ejecuta cada 5 minutos
   - Deberías recibir un email de bienvenida en la bandeja de entrada

---

## 🔧 Solucionar problemas

### Error: "SMTP connection failed"

**Causas posibles:**
- Puerto 587 bloqueado (intenta puerto 465 con SSL)
- Credenciales incorrectas
- El servidor SMTP no responde

**Solución:**
```bash
# En el servidor, ejecuta el test de conexión
php test-smtp-connection.php

# Si falla, verifica:
# 1. El host: appcde.online
# 2. El puerto: 587 (o 465)
# 3. Las credenciales en .env
```

### Los correos no se envían

**Causas posibles:**
- CRON no está configurado
- La tabla `mail_queue` tiene registros atrapados
- Error de SMTP no registrado

**Solución:**
```bash
# Ejecutar manualmente el procesador de cola
php process-queue.php

# Ver qué hay en la cola (en phpMyAdmin)
SELECT * FROM mail_queue WHERE status = 'failed';

# Si hay correos fallidos, ver el error
SELECT id, to_email, status, attempts FROM mail_queue LIMIT 20;
```

### Error 550 o 550 "Unauthorized"

El servidor rechaza el email. **Verificar:**
- ¿El email remitente `no-reply@appcde.online` existe?
- ¿Las credenciales son correctas?
- ¿La contraseña tiene caracteres especiales que necesitan escape?

---

## 📋 Checklist pre-deploy

- [ ] Actualizar `.env` en ambas ubicaciones ✓ (ya hecho)
- [ ] Probar conexión SMTP localmente (opcional)
- [ ] Probar envío de correo (opcional)
- [ ] Subir archivos por FTP
- [ ] Configurar CRON en Don Web
- [ ] Probar crear usuario en admin
- [ ] Verificar que llega el email
- [ ] Si hay errores, ejecutar `php test-smtp-connection.php` en el servidor

---

## 📞 Soporte

Si algo falla:

1. **Lee los logs:**
   ```bash
   # En el servidor
   cat /tmp/mail-queue.log
   ```

2. **Revisa la tabla mail_queue en phpMyAdmin:**
   - Conexión: appcde.online
   - BD: appc_parlamentos
   - Tabla: mail_queue
   - Filtrar por `status = 'failed'`

3. **Ejecuta el test:**
   ```bash
   php test-smtp-connection.php
   php test-send-mail.php prueba@email.com
   ```

---

**Última actualización:** 2026-04-09  
**Configuración:** SMTP appcde.online (servidor propietario)
