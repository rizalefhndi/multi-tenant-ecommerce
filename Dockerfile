# --- Stage 1: Build PHP Vendor (for Ziggy) ---
FROM composer:2.7 AS vendor-builder
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev --ignore-platform-reqs --no-scripts

# --- Stage 2: Build Vue.js Assets ---
FROM node:20-alpine AS asset-builder
WORKDIR /app
COPY package.json ./
RUN npm install --legacy-peer-deps
COPY . .
# Copy vendor dari Stage 1 karena Vue/Vite butuh file vendor/tightenco/ziggy
COPY --from=vendor-builder /app/vendor ./vendor
RUN npm run build

# --- Stage 3: Production PHP + Nginx Server ---
FROM php:8.3-fpm-alpine

# Install system dependencies & PHP extensions yang dibutuhkan Laravel
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    postgresql-dev \
    $PHPIZE_DEPS

# Install pdo_pgsql untuk PostgreSQL
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd

WORKDIR /var/www

# Copy seluruh source code project
COPY . .

# Copy hasil vendor dari Stage 1
COPY --from=vendor-builder /app/vendor ./vendor

# Copy hasil compile Vue dari Stage 2 ke folder public Laravel
COPY --from=asset-builder /app/public/build ./public/build

# Set permission folder storage & bootstrap/cache (Wajib untuk Laravel)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy konfigurasi Nginx
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
