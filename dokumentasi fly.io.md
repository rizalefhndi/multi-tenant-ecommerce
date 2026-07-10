Mantap, pilihan cerdas! Fly.io bakal ngasih kamu fleksibilitas yang hampir mirip VPS karena basisnya Docker container.

Untuk mulai mendeploy project Laravel + Vue Multi-Tenant kamu ke Fly.io, ini step-by-step persiapan awal yang perlu kamu lakukan di komputer lokalmu:

Langkah 1: Install Flyctl (CLI Fly.io)
Fly.io tidak pakai upload zip lewat web dashboard. Semua kontrol (deploy, cek log, koneksi database) dilakukan lewat terminal/CMD menggunakan tool namanya flyctl.

Buka Terminal/PowerShell kamu, lalu install:

Windows (PowerShell):

PowerShell
pwsh -Command "iwr https://fly.io/install.ps1 | iex"
Mac / Linux:

Bash
curl -L https://fly.io/install.sh | sh
Setelah selesai install, jalankan perintah ini untuk login (akan membuka browser):

Bash
fly auth login
Langkah 2: Buat Dockerfile (Menyatukan Laravel + Vue)
Karena project Laravel dan Vue kamu menyatu, kita butuh Dockerfile yang bisa melakukan dua hal saat proses build:

Install node modules & jalankan npm run build (agar Vue di-compile jadi file statis di dalam folder public/build).

Install composer dependencies & jalankan PHP-FPM + Nginx.

Di root project Laravel kamu, buat file baru bernama Dockerfile (tanpa ekstensi) dan masukkan script ini:

Dockerfile
# --- Stage 1: Build Vue.js Assets ---
FROM node:20-alpine AS asset-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- Stage 2: Production PHP + Nginx Server ---
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
    $PHPIZE_DEPS

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy seluruh source code project
COPY . .

# Copy hasil compile Vue dari Stage 1 ke folder public Laravel
COPY --from=asset-builder /app/public/build ./public/build

# Install Laravel dependencies (production mode)
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permission folder storage & bootstrap/cache (Wajib untuk Laravel)
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy konfigurasi Nginx (kita akan buat filenya di Langkah 3)
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
Langkah 3: Setup Konfigurasi Nginx & Supervisor
Buat folder baru bernama docker di root project kamu, lalu buat dua file di dalamnya:

docker/nginx.conf (Konfigurasi Nginx untuk handle wildcard subdomain Laravel):

Nginx
user www-data;
worker_processes auto;
pid /run/nginx.pid;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    sendfile on;
    keepalive_timeout 65;

    server {
        listen 8080 default_server;
        root /var/www/public;
        index index.php index.html;

        # Mendukung wildcard subdomain multi-tenant
        server_name _; 

        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location ~ \.php$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
}
docker/supervisord.conf (Supaya Nginx dan PHP-FPM bisa jalan barengan dalam satu container):

Ini, TOML
[supervisord]
nodaemon=true
logfile=/dev/null
logfile_maxbytes=0

[program:php-fpm]
command=php-fpm
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
Langkah 4: Inisialisasi Aplikasi di Fly.io
Jika file di atas sudah siap, jalankan perintah ini di terminal untuk membuat konfigurasi aplikasi baru di Fly.io:

Bash
fly launch
Yang perlu kamu perhatikan saat proses prompt fly launch:

Choose an app name: Ketik nama project-mu.

Select Organization: Pilih personal akunmu.

Choose a region: Pilih sin (Singapore) supaya akses dari Indonesia ngebut.

Setup a Postgresql database? Pilih Yes. Ini yang bakal jadi database utama dan database multi-tenant kamu nantinya. Pilih spek Development (Free Tier).

Setelah proses selesai, Fly.io otomatis akan membuat file fly.toml di root project kamu.

Sekarang, apakah file-file di atas sudah aman kamu siapkan di projectmu? Jika sudah, kita bisa lanjut ke cara setup environment variable (.env), database migration untuk tenant, dan perintah untuk langsung deploy!