FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpq-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

WORKDIR /var/www/backend

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
