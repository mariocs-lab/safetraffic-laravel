FROM php:8.2-cli

# Instal dependensi sistem dan ekstensi PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev git unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Instal Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setel direktori kerja
WORKDIR /app

# Salin seluruh file proyek dari GitHub ke mesin Render
COPY . .

# Instal pustaka Laravel
RUN composer install --no-dev --optimize-autoloader

# Buka gerbang port untuk Render
EXPOSE 10000

# Jalankan server Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000