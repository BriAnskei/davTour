FROM php:8.3-fpm

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
    pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP-FPM
RUN mv /usr/local/etc/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf.bak
RUN echo "[www]\nuser = www-data\ngroup = www-data\nlisten = 9000\nlisten.owner = www-data\nlisten.group = www-data\npm = dynamic\npm.max_children = 5\npm.start_servers = 2\npm.min_spare_servers = 1\npm.max_spare_servers = 3" > /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
RUN echo 'server {\n    listen 10000;\n    server_name _;\n    root /var/www/html/public;\n    index index.php;\n    location / {\n        try_files $uri $uri/ /index.php?$query_string;\n    }\n    location ~ \.php$ {\n        fastcgi_pass 127.0.0.1:9000;\n        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n        include fastcgi_params;\n    }\n}' > /etc/nginx/sites-enabled/default

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
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# Test file to verify PHP works
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/test.php

EXPOSE 10000

# Start both services
CMD sh -c "php artisan key:generate --force && \
           php artisan migrate --force && \
           php artisan storage:link && \
           php-fpm -D && \
           nginx -g 'daemon off;'"