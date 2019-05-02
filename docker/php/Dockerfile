FROM php:7.0-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt update
RUN apt install -y apache2-utils
RUN a2enmod rewrite