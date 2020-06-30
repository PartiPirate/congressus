FROM php:7.4-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY application/ /var/www/html
EXPOSE 80
# mount config at /var/www/html/config