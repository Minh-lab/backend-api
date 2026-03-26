# ============================================================
# Dockerfile - Backend Laravel (PHP 8.4 CLI)
# ============================================================
FROM php:8.4-cli

# Cài thư viện hệ thống
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    vim \
    jpegoptim optipng pngquant gifsicle \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cài Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy source code
COPY . /var/www

# Tạo .env từ .env.example nếu chưa có
RUN cp -n .env.example .env || true

# Cài dependencies (--no-scripts để tránh lỗi khi chưa có DB)
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# Phân quyền storage và cache
RUN mkdir -p /var/www/storage/logs \
             /var/www/storage/framework/cache \
             /var/www/storage/framework/sessions \
             /var/www/storage/framework/views \
             /var/www/bootstrap/cache \
    && chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Chạy bằng www-data
# Copy entrypoint và cấp quyền thực thi (phải làm khi còn root)
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER www-data

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
