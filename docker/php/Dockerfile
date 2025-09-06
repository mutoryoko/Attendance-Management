FROM php:8.4-fpm

COPY php.ini /usr/local/etc/php/

RUN apt-get update && apt-get install -y \
    default-mysql-client \
    zlib1g-dev \
    libzip-dev \
    unzip \
    libonig-dev \
    libxml2-dev \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www