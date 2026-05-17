FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    zip \
    curl \
    git \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libssl-dev \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Nginx
COPY nginx.conf /etc/nginx/nginx.conf
COPY default.conf /etc/nginx/sites-enabled/default

# Setup Laravel
WORKDIR /var/www/html
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Install composer dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Generate optimized caches
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 10000

# Start PHP-FPM and Nginx
CMD sh -c "php artisan migrate --force && \
           php artisan storage:link && \
           php-fpm -D && \
           nginx -g 'daemon off;'"