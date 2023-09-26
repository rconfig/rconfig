Simple, clone the develop branch of https://github.com/rconfig/rconfig
Copy .env.docker.exampe to .env and edit any or all of the following
DB_CONNECTION=mysql
DB_HOST=DBHOST
DB_PORT=3306
DB_DATABASE=DBNAME
DB_USERNAME=DBUSER
DB_PASSWORD=DBPASS
DB_STORAGE_LOCATION=/storage/app/rconfig/mysql

REDIS_HOST=rconfig-redis
REDIS_PASSWORD=null
REDIS_PORT=6379

#DOCKER EXPOSED PORTS
EXPOSED_APP_PORT=8080
EXPOSED_DB_PORT=3307
EXPOSED_HORIZON_PORT=8081
EXPOSED_REDIS_PORT=7000

Docker compose up

After the containers are up and active successfully, deploy the app
docker-compose exec php-apache /bin/bash
cd /var/www/html && yes | composer install --no-dev
cd /var/www/html && php artisan install
And that should be it. Launch the website/ server on port 8080, and login with admin@domain.com and admin.

```bash
docker compose exec php-apache /bin/bash
```

```bash
cd /var/www/html && yes | composer install --no-dev
```

```bash
cd /var/www/html && php artisan install

```
