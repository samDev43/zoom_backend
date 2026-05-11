FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    pkg-config \
    && docker-php-ext-install pdo pdo_pgsql pgsql \
    && docker-php-ext-enable pgsql pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN mkdir -p /var/www/html/api/public/uploads \
    && chown -R www-data:www-data /var/www/html/api/public/uploads \
    && chmod -R 755 /var/www/html/api/public/uploads