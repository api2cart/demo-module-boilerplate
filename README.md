# API2CartBoilerplate

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/api2cart/api2cart-php-sdk"><img src="https://poser.pugx.org/api2cart/api2cart-php-sdk/d/total.svg" alt="Total Downloads"></a>
</p>

## Usage

To get started, make sure you have [Docker installed and runned](https://docs.docker.com/docker-for-mac/install/) on your system


### Automatic start
1. Be sure you have Docker installed and runned on your PC.

2. please run `start_me.sh` (for Linux/Unix/MacOS) or `start_me.bat` (for Windows) 




### Manual start
First open a terminal and from this cloned respository's root run `docker-compose up -d --build`. 
Open up your browser of choice to [http://localhost:8080](http://localhost:8080) and you should see your Laravel app running as intended.  

Three new containers have been added that handle Composer, NPM, and Artisan commands without having to have these platforms installed on your local computer. Use the following command templates from your project root, modifiying them to fit your particular use case:

- `docker-compose run app cp .env.example .env`
- `docker-compose run --rm composer update`
- `docker-compose run --rm artisan migrate`
- `docker-compose run --rm artisan db:seed`  

in same cases (Windows OS) you can got error when run 2 last command, so please yse those instead:

- `docker-compose run app php artisan migrate`
- `docker-compose run app php artisan db:seed`



##Use credentials to login:

- Login: **admin@local.com**
- Password: **123456**


Containers created and their ports (if used) are as follows:

- **nginx** - `:8080`
- **mysql** - `:3306`
- **php** - `:9000`
- **npm**
- **composer**
- **artisan**