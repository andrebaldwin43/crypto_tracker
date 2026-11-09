FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    build-essential \
    git \
    unzip \
    libzip-dev \
    libicu-dev \
    libssl-dev \
    libsasl2-dev \
    pkg-config \
    && docker-php-ext-install zip intl opcache \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
