# laravel.cron.Dockerfile
FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk update && apk add supervisor bash

# # Create the log file to be able to run tail
RUN touch /var/log/cron.log

# RUN apt-get update && apt-get install -y cron supervisor
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configure cron
# RUN crontab -l | { cat; echo "* * * * * cd /var/www/html/current && /usr/local/bin/php artisan schedule:run >> /var/log/cron.log 2>&1"; } | crontab -
COPY crontab /etc/crontabs/root

# RUN crontab -l

# Configure supervisor
COPY supervisord.conf /etc/supervisor/supervisord.conf

# CMD  tail -f /var/log/cron.log  & /usr/bin/supervisord -c /etc/supervisor/supervisord.conf

ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
