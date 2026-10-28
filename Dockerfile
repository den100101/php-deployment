
FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html/

RUN test -f /var/www/html/login.php

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
