FROM php:8.0.0RC4-fpm-buster

RUN apt-get update && apt-get install -y \
    libpq-dev \
    wget \
    zlib1g-dev \
    libmcrypt-dev \
    libzip-dev \
    openssl

RUN docker-php-ext-install pdo pdo_mysql bcmath zip

WORKDIR /app