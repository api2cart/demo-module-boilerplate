FROM php:7.4-fpm-alpine

WORKDIR /var/www/html

COPY src/.env.example /var/www/html/.env

RUN apk add --update libzip-dev curl-dev &&\
    docker-php-ext-install curl

RUN docker-php-ext-install pdo pdo_mysql

# xdebug with PHPSHTORM
ENV XDEBUG_VERSION=2.9.2
RUN apk --no-cache add --virtual .build-deps \
    g++ \
    autoconf \
    make && \
    pecl install xdebug-${XDEBUG_VERSION} && \
    docker-php-ext-enable xdebug && \
    apk del .build-deps && \
    rm -r /tmp/pear/* && \
    apk del gcc g++ &&\
    rm -rf /var/cache/apk/*

# Change TimeZone
RUN apk add --update tzdata
ENV TZ=Europe/Kiev

ADD var/etc/php /usr/local/etc/php

#Install composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN apk --no-cache add shadow && usermod -u 1000 www-data; \
    chown -R www-data:www-data /var/www/html && chmod 777 -R /var/log

