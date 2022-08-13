FROM php:8.1.8-fpm as php

RUN apt-get update && apt-get install -y make curl libzip-dev zip unzip \
    docker-php-ext-install zip bcmath; \
    apt-get clean; \
    rm -rf /var/lib/apt

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/bin/
RUN install-php-extensions zip bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock /app/

RUN composer install --prefer-dist --no-dev --no-ansi --no-scripts --no-interaction --no-progress

COPY . /app

RUN composer dump-autoload
