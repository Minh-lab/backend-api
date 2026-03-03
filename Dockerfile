FROM php:8.2-cli

# Cài đặt thư viện hệ thống (Đã bao gồm hỗ trợ Excel, Image, Zip)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip unzip git curl \
    libonig-dev libxml2-dev libzip-dev

# Cài đặt PHP extensions quan trọng
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
CMD php artisan serve --host=0.0.0.0 --port=8000