# Menggunakan image PHP-FPM (tanpa web server bawaan)
FROM php:8.3-fpm

# Mengatur folder kerja di dalam container
WORKDIR /var/www/html

# Menginstal dependensi sistem linux yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# Menginstal ekstensi PHP (termasuk pdo_mysql untuk database)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Pengerasan PHP: jangan bocorkan versi/error, batasi sesi, matikan path-info exploit
RUN printf '%s\n' \
    'expose_php=Off' \
    'display_errors=Off' \
    'display_startup_errors=Off' \
    'log_errors=On' \
    'cgi.fix_pathinfo=0' \
    'session.use_strict_mode=1' \
    'session.use_only_cookies=1' \
    'session.cookie_httponly=1' \
    'session.cookie_samesite=Lax' \
    'allow_url_include=Off' \
    > /usr/local/etc/php/conf.d/zz-security.ini

# Mengambil Composer terbaru untuk package Laravel
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
