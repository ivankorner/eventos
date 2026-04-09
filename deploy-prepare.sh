#!/bin/bash

##############################################################################
# Script de Preparación para Deploy a Don Web
#
# Uso: bash deploy-prepare.sh
#
# Este script prepara todos los archivos necesarios para migrar desde XAMPP
# a un servidor compartido de Don Web.
##############################################################################

set -e  # Salir si hay error

echo "=========================================="
echo "Sistema de Inscripciones — Deploy Prep"
echo "=========================================="
echo ""

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Función para imprimir en color
success() {
    echo -e "${GREEN}✓ $1${NC}"
}

error() {
    echo -e "${RED}✗ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

##############################################################################
# Paso 1: Crear carpeta de deploy
##############################################################################

echo ""
echo "📁 Paso 1: Creando carpeta de preparación..."

DEPLOY_DIR="./deploy-files"
BACKUP_DIR="./backups"

mkdir -p "$DEPLOY_DIR"
mkdir -p "$BACKUP_DIR"

success "Carpetas creadas: $DEPLOY_DIR y $BACKUP_DIR"

##############################################################################
# Paso 2: Exportar Base de Datos
##############################################################################

echo ""
echo "📊 Paso 2: Exportando base de datos..."

DB_USER="${DB_USER:-root}"
DB_NAME="${DB_NAME:-inscripciones_db}"
DB_PASS="${DB_PASS:-}"

TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/inscripciones_db_${TIMESTAMP}.sql"

if command -v mysqldump &> /dev/null; then
    if [ -z "$DB_PASS" ]; then
        mysqldump -u "$DB_USER" "$DB_NAME" > "$BACKUP_FILE"
    else
        mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_FILE"
    fi

    if [ -f "$BACKUP_FILE" ]; then
        FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
        success "BD exportada: $BACKUP_FILE ($FILE_SIZE)"
        # Copiar también a deploy-files para fácil acceso
        cp "$BACKUP_FILE" "$DEPLOY_DIR/"
    else
        error "No se pudo exportar la BD"
        exit 1
    fi
else
    warning "mysqldump no encontrado. Salta exportación manual."
    warning "Ejecutar en terminal: mysqldump -u root inscripciones_db > $DEPLOY_DIR/database_backup.sql"
fi

##############################################################################
# Paso 3: Instalar dependencias Composer
##############################################################################

echo ""
echo "📦 Paso 3: Instalando dependencias Composer..."

if ! command -v composer &> /dev/null; then
    error "Composer no está instalado"
    echo "Descargarlo desde: https://getcomposer.org"
    exit 1
fi

echo "Ejecutando: composer install --no-dev --optimize-autoloader"
composer install --no-dev --optimize-autoloader

if [ -d "vendor" ]; then
    VENDOR_SIZE=$(du -sh vendor | cut -f1)
    success "Dependencias instaladas: vendor/ ($VENDOR_SIZE)"
else
    error "vendor/ no se creó correctamente"
    exit 1
fi

##############################################################################
# Paso 4: Preparar archivos de configuración
##############################################################################

echo ""
echo "⚙️  Paso 4: Preparando configuración..."

# Copiar .env.production a deploy
if [ -f ".env.production" ]; then
    cp .env.production "$DEPLOY_DIR/.env"
    success "Archivo .env preparado (renombrado de .env.production)"
    echo "  ⚠️  IMPORTANTE: Editar $DEPLOY_DIR/.env con credenciales reales de Don Web"
else
    error ".env.production no existe. Crear primero."
    exit 1
fi

# Crear archivo de notas
cat > "$DEPLOY_DIR/CREDENCIALES_DONWEB.txt" << 'EOF'
CREDENCIALES NECESARIAS (proporciona Don Web)
==============================================

Base de Datos:
  - Host: ___________________
  - Usuario: ________________
  - Contraseña: _____________
  - Nombre BD: ______________

Dominio:
  - URL: https://________________/eventos/public/

FTP/SFTP:
  - Host: ____________________
  - Usuario: __________________
  - Contraseña: _______________
  - Puerto: 21 (FTP) o 22 (SFTP)
  - Ruta: /eventos/

Actualizar estas credenciales en el archivo .env
EOF

success "Archivo de notas creado: $DEPLOY_DIR/CREDENCIALES_DONWEB.txt"

##############################################################################
# Paso 5: Crear lista de archivos a subir
##############################################################################

echo ""
echo "📋 Paso 5: Generando lista de archivos..."

cat > "$DEPLOY_DIR/FILES_TO_UPLOAD.txt" << 'EOF'
ARCHIVOS A SUBIR VÍA FTP
=======================

OBLIGATORIOS:
  ✓ app/                          (Código de la aplicación)
  ✓ config/                       (Configuración)
  ✓ routes/                       (Rutas)
  ✓ database/                     (Scripts SQL)
  ✓ public/                       (Archivos públicos)
  ✓ vendor/                       (Dependencias - muy pesado)
  ✓ composer.json
  ✓ composer.lock
  ✓ .env                          (Renombrado de .env.production)
  ✓ .htaccess                     (Reescritura de URLs)

OPCIONALES:
  • .git/                         (Control de versiones)
  • tests/                        (Tests unitarios)

NUNCA SUBIR:
  ✗ .DS_Store                     (archivos macOS)
  ✗ storage/                      (Se crea automáticamente)
  ✗ .env.* archivos originales    (Solo subir como .env)

DESPUÉS DE SUBIR (en el servidor):
  $ chmod 755 app config routes database public
  $ chmod 644 app/**/*.php config/**/*.php
  $ chmod 775 public/uploads public/uploads/*
  $ chmod 600 .env

TAMAÑO APROXIMADO:
  - app/: ~5 MB
  - config/: ~100 KB
  - vendor/: ~50-70 MB (lo más pesado)
  - public/: ~20 MB (incluye uploads)
  - Total: ~100 MB
EOF

success "Creado: $DEPLOY_DIR/FILES_TO_UPLOAD.txt"

##############################################################################
# Paso 6: Verificaciones finales
##############################################################################

echo ""
echo "✅ Paso 6: Verificaciones finales..."

CHECKS_PASSED=0
CHECKS_TOTAL=0

# Verificar archivos clave
check_file() {
    CHECKS_TOTAL=$((CHECKS_TOTAL + 1))
    if [ -f "$1" ]; then
        success "✓ $1"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
    else
        error "✗ $1 no existe"
    fi
}

echo ""
echo "Verificando archivos clave:"
check_file "composer.json"
check_file "composer.lock"
check_file ".env.production"
check_file "public/index.php"
check_file ".htaccess"

# Verificar directorios
echo ""
echo "Verificando directorios:"
for dir in app config routes public vendor database; do
    CHECKS_TOTAL=$((CHECKS_TOTAL + 1))
    if [ -d "$dir" ]; then
        SIZE=$(du -sh "$dir" 2>/dev/null | cut -f1)
        success "✓ $dir/ ($SIZE)"
        CHECKS_PASSED=$((CHECKS_PASSED + 1))
    else
        error "✗ $dir/ no existe"
    fi
done

##############################################################################
# Resumen
##############################################################################

echo ""
echo "=========================================="
echo "📊 RESUMEN"
echo "=========================================="
echo ""
echo "Verificaciones: $CHECKS_PASSED/$CHECKS_TOTAL ✓"
echo ""
echo "Archivos preparados en: $DEPLOY_DIR"
echo "Backups guardados en: $BACKUP_DIR"
echo ""

if [ $CHECKS_PASSED -eq $CHECKS_TOTAL ]; then
    success "Todo listo para el deploy"
    echo ""
    echo "PRÓXIMOS PASOS:"
    echo "1. Revisar $DEPLOY_DIR/CREDENCIALES_DONWEB.txt"
    echo "2. Editar $DEPLOY_DIR/.env con datos reales de Don Web"
    echo "3. Leer DEPLOYMENT_GUIDE.md (guía paso a paso)"
    echo "4. Subir archivos vía FTP a /eventos"
    echo "5. Importar BD desde $DEPLOY_DIR/inscripciones_db_*.sql"
    echo ""
else
    error "Hay problemas por resolver antes de hacer deploy"
    exit 1
fi

echo "=========================================="
echo ""
