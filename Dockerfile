# 1. Builder Stage (PHP + Node)
FROM php:8.3-cli-alpine AS builder

# Instalar Node.js, NPM y git
RUN apk add --no-cache nodejs npm git

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1.1 Instalar dependencias de PHP primero
COPY composer.json composer.lock ./
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --no-dev

# 1.2 Copiar el código fuente y optimizar autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev

# 1.3 Instalar dependencias de Frontend y Compilar
# Ahora PHP está disponible, así que los plugins de Vite que usen artisan funcionarán
RUN npm ci
RUN npm run build \
    && rm -rf node_modules # Limpiamos para no copiar esta carpeta pesada a la imagen final

# 2. Final Production Image
FROM serversideup/php:8.3-fpm-nginx-alpine

WORKDIR /var/www/html

# Copiar todo el proyecto compilado desde el builder
COPY --from=builder --chown=999:999 /app/ ./

# Limpiar y optimizar cachés de Laravel
RUN php artisan optimize:clear \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache
