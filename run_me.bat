@ECHO OFF

SET mypath=%cd%
SET CONFIGFILE=%mypath%\src\.env

copy %mypath%\hooks\pre-commit %mypath%\.git\hooks

IF EXIST %CONFIGFILE% (
    echo Config exists
) ELSE (
    copy %mypath%\src\.env.example %CONFIGFILE%
)

docker-compose down

if errorlevel 1 (
    echo Please check if Docker installed and runned.
    pause 0
    start "" https://www.docker.com/products/docker-desktop
    exit
)

docker-compose up -d

docker-compose run --rm composer update

docker-compose run app php artisan migrate

docker-compose run app php artisan db:seed

start "" http://localhost:8080
