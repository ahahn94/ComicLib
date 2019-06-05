FROM php:7.0-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt update
RUN apt install -y apache2-utils libapache2-mod-xsendfile libzip-dev zlib1g-dev libmagickwand-dev
RUN docker-php-ext-install zip
RUN pecl install rar && docker-php-ext-enable rar
RUN yes "" | pecl install imagick && docker-php-ext-enable imagick
RUN a2enmod rewrite