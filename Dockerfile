# rConfig V8 Core - Docker Image
FROM php:8.4-apache-bookworm

# Set user ID for www-data to 1000
RUN usermod -u 1000 www-data

RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    default-libmysqlclient-dev \
    libldap2-dev \
    libcurl4-openssl-dev \
    libgmp-dev \
    libonig-dev \
    libicu-dev \
    zip \
    vim \
    redis-server \
    unzip \
    supervisor \
    cron \
    netcat-openbsd \
    libsnmp-dev \
    snmp \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure zip \
    && docker-php-ext-install gd zip pdo pdo_mysql pcntl snmp ldap curl mbstring fileinfo gmp intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Configure Apache to use Laravel's public directory
RUN echo "DocumentRoot /var/www/html/rconfig/public" > /etc/apache2/sites-available/000-default.conf \
    && echo "<Directory /var/www/html/rconfig/public>" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    AllowOverride All" >> /etc/apache2/sites-available/000-default.conf \
    && echo "    Require all granted" >> /etc/apache2/sites-available/000-default.conf \
    && echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/rconfig

# Dependency layer: only invalidated when composer files change
COPY composer.json composer.lock ./
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Application source (this is the checked-out tag, no clone)
COPY . .

RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload --optimize --no-dev

# Copy service configuration files
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/redis.conf /etc/redis/rconfig-redis.conf

# Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Create log and runtime directories
RUN mkdir -p /var/log /var/run/supervisor /var/run/redis /var/lib/redis \
    && chown -R www-data:www-data /var/log \
    && chown -R redis:redis /var/run/redis /var/lib/redis

# Set up permissions for storage and cache
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/rconfig/storage /var/www/html/rconfig/bootstrap/cache \
    && chmod -R 775 /var/www/html/rconfig/storage/framework /var/www/html/rconfig/storage/logs /var/www/html/rconfig/bootstrap/cache \
    && chmod 775 /var/www/html/rconfig/storage
# Note: the chmod above deliberately targets framework/logs/cache rather than
# recursing over all of storage/. storage/app/rconfig/data holds downloaded
# device configs, which carry device secrets and are kept non world readable by
# FileOperations. A recursive chmod here would undo that on every build.

# Marks the runtime as containerised. rconfig:clear-all keys off this to skip
# the bare-metal supervisor/systemctl restarts (which need sudo) and to manage
# storage permissions the container way. Kept low in the file so it does not
# invalidate the apt and composer layers above it.
ENV IS_DOCKER=true

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]