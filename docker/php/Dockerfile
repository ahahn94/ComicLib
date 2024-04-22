FROM php:7.4-apache-bullseye

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN apt update
RUN apt install -y apache2-utils libapache2-mod-xsendfile libzip-dev zlib1g-dev libmagickwand-dev
RUN docker-php-ext-install zip
RUN pecl install rar && docker-php-ext-enable rar
RUN yes "" | pecl install imagick && docker-php-ext-enable imagick
RUN openssl req -x509 -nodes -days 730 -newkey rsa:2048 -subj "/CN=comiclib.local" -keyout /etc/ssl/private/apache-selfsigned.key -out /etc/ssl/certs/apache-selfsigned.crt
RUN apt install -y certbot python3-certbot-apache
RUN a2enmod ssl
RUN a2enmod headers
RUN a2enmod rewrite