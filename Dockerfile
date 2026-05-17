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
    procps \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure PHP-FPM to listen on port 9000
RUN sed -i 's/listen = \/run\/php\/php8.3-fpm.sock/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf || true
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf || true

# Configure Nginx
RUN echo 'server {\n\
    listen 10000;\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
    add_header X-Frame-Options "SAMEORIGIN" always;\n\
    add_header X-Content-Type-Options "nosniff" always;\n\
    add_header X-XSS-Protection "1; mode=block" always;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        fastcgi_param PATH_INFO $fastcgi_path_info;\n\
        include fastcgi_params;\n\
    }\n\
    location ~ /\.ht {\n\
        deny all;\n\
    }\n\
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf)$ {\n\
        expires 1y;\n\
        add_header Cache-Control "public, immutable";\n\
    }\n\
}' > /etc/nginx/sites-enabled/default

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

# Create test file
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/test.php

# Create health check file
RUN echo "<?php echo 'OK'; ?>" > /var/www/html/public/health.txt

EXPOSE 10000

# Create startup script
RUN echo '#!/bin/bash\n\
echo "=== Starting Laravel on Render ===\n\
echo "Environment: ${APP_ENV:-production}\n\
echo "App URL: ${APP_URL}\n\
\n\
# Create .env if needed\n\
if [ ! -f .env ]; then\n\
    echo "Creating .env file..."\n\
    touch .env\n\
fi\n\
\n\
# Generate key\n\
echo "Generating app key..."\n\
php artisan key:generate --force\n\
\n\
# Run migrations\n\
echo "Running migrations..."\n\
php artisan migrate --force\n\
\n\
# Create storage link\n\
echo "Creating storage link..."\n\
php artisan storage:link\n\
\n\
# Clear old caches\n\
php artisan optimize:clear\n\
\n\
# Cache config and routes\n\
php artisan config:cache\n\
php artisan route:cache\n\
php artisan view:cache\n\
\n\
# Test PHP-FPM config\n\
echo "Testing PHP-FPM config..."\n\
php-fpm -t\n\
\n\
# Test Nginx config\n\
echo "Testing Nginx config..."\n\
nginx -t\n\
\n\
# Start PHP-FPM in background\n\
echo "Starting PHP-FPM..."\n\
php-fpm -D\n\
\n\
# Wait for PHP-FPM to be ready\n\
sleep 2\n\
\n\
# Check if PHP-FPM is running\n\
if pgrep php-fpm > /dev/null; then\n\
    echo "PHP-FPM started successfully"\n\
else\n\
    echo "ERROR: PHP-FPM failed to start"\n\
    exit 1\n\
fi\n\
\n\
# Start Nginx in foreground\n\
echo "Starting Nginx..."\n\
nginx -g "daemon off;"\n\
' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]