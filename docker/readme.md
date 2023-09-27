To setup docker for rCOnfig v6 Core, clone the develop branch of https://github.com/rconfig/rconfig

Docker, docker compose and git are required to setup rConfig v6 Core for Docker.

```bash
git clone https://github.com/rconfig/rconfig
cd rconfig
git checkout develop
```

Copy .env.docker.example to .env

```bash
cp .env.docker.example .env
```

Edit following lines in .env file. Those with a \* are required. The rest are optional.

```bash
APP_DEBUG=false #(recommended: false/ true for development)
DB_HOST=rconfig-mariadb-1 #(Do not change unless you know what you are doing)
DB_PORT=3306
DB_DATABASE=DBNAME* #(recommended: rconfig)
DB_USERNAME=DBUSER* #(recommended: anything but root)
DB_PASSWORD=DBPASS* #(recommended: 16+ characters, alphanumeric, special characters)
DB_STORAGE_LOCATION=/storage/app/rconfig/mysql

#DOCKER EXPOSED PORTS - DO NOT CHANGE UNLESS YOU KNOW WHAT YOU ARE DOING
APP_PORT=8080
FORWARD_DB_PORT=3307
FORWARD_REDIS_PORT=7000
```

```bash
docker compose up -d
```

After the containers are up and active successfully, deploy the app

```bash
docker exec -it rconfig-rconfig.core-1 /bin/bash
```

```bash
cd /var/www/html && yes | composer install --no-dev
```

```bash
cd /var/www/html && php artisan install
php artisan rconfig:clear-all
```

Exit the container, and set the storage permissions (this is a temporary fix, and will be fixed in the next release)

```bash
sudo chmod -R 777 storage && sudo chmod -R 777 bootstrap/cache
```

Might be best to bring down the containers and bring them back up again.

```bash
docker compose down
docker compose up -d
```

And that should be it. Launch the website/ server on port 8080, and login with admin@domain.com and admin.

If you run into difficulties, please open an issue on github.

Please note, the dockerkillall.sh script is for development purposes only. It will kill all docker containers and remove all docker images. It is not recommended for production use.
