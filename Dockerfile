FROM php:8.0-apache

HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
  CMD php scripts/healthcheck.php

COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# To use htaccess
RUN a2enmod rewrite

# Required from ext-gd
RUN apt-get update --fix-missing \
  && apt-get install -y zlib1g-dev libpng-dev libwebp-dev libjpeg-dev libfreetype6-dev

RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
  && docker-php-ext-install pdo_mysql gd

RUN chown -R www-data:www-data /var/www/html

USER root
