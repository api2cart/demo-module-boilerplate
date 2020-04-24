#!/bin/bash

dir=$(pwd)

#check if config exists
CONFIGFILE="$dir/src/.env"
if [ -f "$CONFIGFILE" ]; then
    echo "config $CONFIGFILE exist"
else
    echo "config do not exists"
    cp "$dir/src/.env.example" "$dir/src/.env"
fi

# give rw permissions for temorary folder
sudo chmod -R 777 $dir/src/storage

#stop for any case container
docker-compose down

#run containers in background
docker-compose up -d

if [ $? -eq 0 ];
then
    echo "Looks all ok, update related dependencies..."
    docker-compose run --rm composer update
    docker-compose run app php artisan migrate
    docker-compose run app php artisan db:seed
else
     echo "missing start please contact support"
     exit
fi

xdg-open http://localhost:8080
