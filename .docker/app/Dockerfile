FROM php:8.4-fpm

# UID/GID хостового пользователя прокинем из docker-compose
ARG UID=1000
ARG GID=1000

RUN apt-get update \
    && apt-get install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip libpq-dev libpng-dev libwebp-dev libxpm-dev \
    && docker-php-ext-install intl opcache pdo pgsql pdo_mysql pdo_pgsql gd \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    # Устанавливаем Node.js и npm
    && apt-get install -y nodejs \
    # Проверяем установку
    && node --version \
    && npm --version

RUN groupmod -g "${GID}" www-data \
 && usermod -u "${UID}" -g "${GID}" www-data

WORKDIR /var/www/rns_telcom

COPY ./conf/prod.ini /usr/local/etc/php/conf.d/prod.ini

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -sS https://get.symfony.com/cli/installer | bash

USER root
