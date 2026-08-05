# 1. Build Frontend Assets (Node)
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# 2. Build Backend Dependencies (Composer)
FROM composer:2 AS vendor
WORKDIR /app
COPY database/ database/
COPY composer.json composer.lock ./
# Ignoramos dependencias de plataforma (como extensiones de php) en esta etapa para evitar errores
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-dev
COPY . .
RUN composer dump-autoload --optimize --no-dev

# 3. Final Production Image
FROM serversideup/php:8.3-fpm-nginx-alpine

# El usuario 'webuser' (UID 999) es el predeterminado en esta imagen
WORKDIR /var/www/html

# (Opcional) Instalar extensiones adicionales de PHP si Laravel las requiere. 
# La imagen ya incluye pdo_mysql, mbstring, redis, bcmath, entre otras.

# Copiar dependencias de Composer
COPY --from=vendor --chown=999:999 /app/vendor/ ./vendor/

# Copiar assets compilados de Vite
COPY --from=frontend --chown=999:999 /app/public/build/ ./public/build/

# Copiar el resto del código del proyecto
COPY --chown=999:999 . .

# Limpiar y optimizar cachés de Laravel
# Nota: Durante el build, no hay acceso a la BD real, así que solo cacheamos vistas/rutas/config
RUN php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache
