FROM php:8.0-fpm-alpine3.13

ADD php/php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www/html

COPY src/.env.example /var/www/html/.env

RUN apk add --update libzip-dev curl-dev &&\
    docker-php-ext-install curl && \
    apk del gcc g++ &&\
    rm -rf /var/cache/apk/*

RUN docker-php-ext-install pdo pdo_mysql


RUN apk --no-cache add shadow && usermod -u 1000 www-data; \
    chown -R www-data:www-data /var/www/html
