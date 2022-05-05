FROM php:7.4-fpm

RUN apt-get update -yqq && apt-get install -y \
     git \
     libzip-dev \
     libz-dev \
     libmemcached-dev

RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    zip

RUN pecl install memcached
RUN docker-php-ext-enable memcached
