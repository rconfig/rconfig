# laravel.cron.Dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

# Installing dependencies
RUN apk update && apk add vim wget curl mysql-client git supervisor bash

RUN apk add php82-pdo php82-pdo_mysql php82-mysqli php82-zip php82-fileinfo php82-bcmath php82-gmp php82-zip php82-curl php82-mbstring php82-gd 
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Install pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

# Configure supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]

