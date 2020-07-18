FROM php:7.4-fpm-alpine
RUN docker-php-ext-install pdo pdo_mysql
RUN apk update && \
    apk upgrade && \
    docker-php-source extract && \
    apk add --no-cache --virtual .build-dependencies  \
            cyrus-sasl-dev  git autoconf g++ libtool  \
            make libgcrypt && \
    apk add --no-cache \
            libmemcached-dev libzip-dev zlib-dev && \
    git clone https://github.com/php-memcached-dev/php-memcached.git /usr/src/php/ext/memcached/    && \
    docker-php-ext-configure memcached  &&  \
    docker-php-ext-install -j"$(getconf _NPROCESSORS_ONLN)"  zip memcached && \
    pecl install memcache && \
    docker-php-ext-enable memcache && \
    apk del .build-dependencies && \
    docker-php-source delete && \
    rm -rf /tmp/* /var/cache/apk/*
