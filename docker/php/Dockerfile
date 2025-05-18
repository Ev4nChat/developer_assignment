FROM php:8.2-fpm

COPY php.ini /usr/local/etc/php/conf.d/custom.ini

RUN apt-get update && apt-get install -y \
    git zip unzip libzip-dev libpq-dev libonig-dev libxml2-dev \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-install pdo pdo_mysql zip

RUN echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/backend

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
