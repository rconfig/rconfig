# docker-compose down
docker rm -f $(docker ps -a -q)
docker volume rm $(docker volume ls -q)
rm -fr mysql/*
rm -fr src/rconfig

docker-compose up -d -build
docker ps -a
docker rmi $(docker images -a -q)
docker rmi $(docker images --filter dangling=true -q)
docker system df

rm -fr /var/www/html/rconfig/storage/app/rconfig/mysql

# docker compose exec php-apache /bin/bash