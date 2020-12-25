FROM php:7.3-apache

COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# To use htaccess
RUN a2enmod rewrite

# Required from ext-gd
RUN apt-get update --fix-missing
RUN apt-get install -y zlib1g-dev libpng-dev libwebp-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp

RUN docker-php-ext-install pdo_mysql gd

USER root