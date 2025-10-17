<p align="center"><h1 align="center">Todo List App</h1></p>

## About Project

Todo List App is a webapps project created with laravel, can be used for creating todo, export todo as excel and provide data summary of todo for chart.

## Project Setup

```bash
# Clone project
$ git clone https://github.com/fidojahfal/laravel-todolistapp-restapi.git

# Go to directory
$ cd ./laravel-todolistapp-restapi

# Install depedency
$ composer install

# Copy file environment
$ cp .env.example .env

# Generate application key
$ php artisan key:generate

# Run container Docker (MySql)
$ docker-compose up -d

# Run migrate database
$ php artisan migrate

# Run the application NestJS
# Development
$ php artisan serve

```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
