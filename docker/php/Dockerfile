FROM php:7.4-fpm

COPY php.ini $PHP_INI_DIR/php.ini

RUN pear config-set php_ini $PHP_INI_DIR/php.ini

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        libzip-dev \
        zlib1g-dev \
        libxml2-dev \
        libonig-dev \
        librabbitmq-dev \
    && docker-php-ext-install \
        opcache \
        bcmath \
        intl \
        sockets \
        zip \
    && pecl install \
        ds \
        amqp

RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

WORKDIR /var/www/project/
