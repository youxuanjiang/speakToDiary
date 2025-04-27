FROM php:8.2-fpm

# 系統安裝
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev \
    libzip-dev ffmpeg python3 python3-pip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl zip

# Composer 安裝
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# 設定工作目錄
WORKDIR /var/www

COPY docker/laravel/php.ini /usr/local/etc/php/

CMD ["php-fpm"]