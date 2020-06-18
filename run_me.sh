#!/bin/bash

dir=$(pwd)

# setup user uid:gid for composer
uid="$(id -u):$(id -g)"
echo "uid=$uid" > "$dir/.env"

cp "$dir/hooks/pre-commit" "$dir/.git/hooks"

#overwrite config
CONFIGFILE="$dir/src/.env"

cp "$dir/src/.env.example" "$dir/src/.env"

# give rw permissions for temorary folder
sudo chmod -R 777 $dir/src/storage

#stop for any case container
if ! [ -x "$(command -v docker-compose)" ]; then
  echo "Please check if Docker installed and runned."

  if ! [ -x "$(command -v xdg-open)" ]; then
    open https://docs.docker.com/get-docker/
    exit 1
  fi

  xdg-open https://docs.docker.com/get-docker/
  exit 1
fi

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

if ! [ -x "$(command -v xdg-open)" ]; then
    open http://localhost:8080
    exit 0
fi
xdg-open http://localhost:8080
