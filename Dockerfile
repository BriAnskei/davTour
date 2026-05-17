FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    unzip zip curl git libpng-dev libjpeg-dev libfreetype6-dev libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN cp .env.example .env || true

RUN php artisan key:generate || true

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000