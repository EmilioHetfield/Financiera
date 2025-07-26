FROM php:8.2-apache

# Instala pdo_mysql, mysqli y dependencias básicas
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo pdo_mysql mysqli
