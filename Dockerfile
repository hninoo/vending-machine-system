ARG PHP_VERSION=8.5

FROM php:${PHP_VERSION}-fpm-alpine

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && apk add --no-cache \
        git \
        unzip \
        libzip-dev \
    && docker-php-ext-install \
        pdo_mysql \
        zip \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
