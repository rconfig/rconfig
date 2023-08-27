FROM php:8.2-apache

# Installing dependencies
RUN apt-get update && apt-get install -y vim wget curl default-mysql-client git

RUN apt-get install -y software-properties-common

RUN apt install -y g++ libicu-dev libpq-dev libzip-dev zip zlib1g-dev libcurl4-openssl-dev libonig-dev libgmp-dev libpng-dev libwebp-dev iputils-ping telnet ssh \
    && docker-php-ext-install intl opcache pdo pdo_mysql mysqli zip fileinfo bcmath gmp zip curl mbstring gd 

# Install pcntl
RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

# Install node and npm
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# output node and npm versions
RUN node -v && npm -v

# install composer 2 
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN echo 'export COMPOSER_ALLOW_SUPERUSER=1' >>~/.bashrc
RUN echo 'export PATH=~/.config/composer/vendor/bin:$PATH' >>~/.bashrc
RUN echo 'export PATH="$PATH:$HOME/.config/composer/vendor/bin"' >>~/.bashrc
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN chown -R www-data: /var/www/html
RUN chmod -R 755 /var/www/html
RUN a2enmod rewrite && service apache2 restart

# We must reconfigure here the CMD directive.
CMD apache2-foreground

# docker-compose exec php-apache /bin/bash
# cd /var/www/html && yes | composer install --no-dev
# cd /var/www/html && php artisan install