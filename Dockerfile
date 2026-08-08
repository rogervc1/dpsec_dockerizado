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

# Instalar extensión PHP GD (requerida por dompdf para generar certificados PDF)
USER root
RUN apk add --no-cache freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

WORKDIR /var/www/html

# Copiar todo el proyecto compilado desde el builder
COPY --from=builder --chown=www-data:www-data /app/ ./

# Copiar y preparar el script de inicio automático
COPY docker/startup.sh /etc/entrypoint.d/99-startup.sh

# Cambiar a root para crear carpetas, permisos y preparar el script
USER root

RUN chmod +x /etc/entrypoint.d/99-startup.sh

# Crear carpetas de almacenamiento necesarias y asegurar permisos
RUN mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views storage/app/public \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

