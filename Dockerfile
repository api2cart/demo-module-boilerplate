FROM php:8.0-fpm-alpine

ADD php/php.ini /usr/local/etc/php/conf.d/40-custom.ini

WORKDIR /var/www/html

COPY src/.env.example /var/www/html/.env
RUN echo SSL \
COPY nginx/ssl /etc/
RUN apk add --update libzip-dev curl-dev &&\
    docker-php-ext-install curl && \
    apk del gcc g++ &&\
    rm -rf /var/cache/apk/*

RUN docker-php-ext-install pdo pdo_mysql


RUN usermod -u 1000 www-data; \
    chown -R www-data:www-data /var/www/html

