#!/bin/sh
set -e

echo "🚀 Ejecutando tareas de inicio..."

# 1. Crear carpetas de almacenamiento necesarias
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public

# 2. Asegurar permisos correctos
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Crear symlink de storage si no existe
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
    echo "✅ Storage link creado"
fi

# 4. Limpiar y optimizar caché
php artisan optimize:clear 2>/dev/null || true
echo "✅ Caché limpiada"

echo "🎉 Inicio completado. Arrancando servidor..."
