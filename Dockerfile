FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

COPY src/.env.example /var/www/html/.env

RUN apk add --update libzip-dev curl-dev &&\
    docker-php-ext-install curl && \
    apk del gcc g++ &&\
    rm -rf /var/cache/apk/*

RUN docker-php-ext-install pdo pdo_mysql

RUN usermod -u 1000 www-data; \
    chown -R www-data:www-data /var/www/html

