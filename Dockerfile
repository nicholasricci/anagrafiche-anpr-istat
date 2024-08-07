FROM php:7.1-zts-buster

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --filename=composer --install-dir=/usr/local/bin && \
    php -r "unlink('composer-setup.php');"

RUN apt update -y && apt upgrade -y && \
    apt install -y zlib1g-dev zip libpng-dev && \
    docker-php-ext-install zip gd && \
    docker-php-ext-enable zip gd

WORKDIR /var/www/html