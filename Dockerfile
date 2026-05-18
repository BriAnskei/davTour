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



# Configure PHP-FPM
RUN sed -i 's/listen = .*/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf
RUN echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf

# Configure Nginx
RUN echo 'server {\n\
    listen 10000;\n\
    server_name _;\n\
    root /var/www/html/public;\n\
    index index.php;\n\
    location / {\n\
        try_files $uri $uri/ /index.php?$query_string;\n\
    }\n\
    location ~ \.php$ {\n\
        fastcgi_pass 127.0.0.1:9000;\n\
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;\n\
        include fastcgi_params;\n\
    }\n\
}' > /etc/nginx/sites-enabled/default

# Setup Laravel
WORKDIR /var/www/html
COPY . /var/www/html

# Copy SSL certificate for Aiven database
COPY certs/ca.pem /usr/local/share/ca-certificates/aiven.crt
RUN update-ca-certificates

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html/storage -type d -exec chmod 775 {} \; \
    && find /var/www/html/storage -type f -exec chmod 664 {} \; \
    && find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \; \
    && find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

# Install composer dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Generate optimized caches
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

# Test file to verify PHP works
RUN echo "<?php phpinfo(); ?>" > /var/www/html/public/test.php
RUN echo "OK" > /var/www/html/public/health.txt

EXPOSE 10000

# Create startup script (fixed version)
RUN echo '#!/bin/bash' > /start.sh && \
    echo 'echo "=== Starting Laravel on Render ==="' >> /start.sh && \
    echo 'echo "Environment: ${APP_ENV:-production}"' >> /start.sh && \
    echo 'echo "App URL: ${APP_URL}"' >> /start.sh && \
    echo '' >> /start.sh && \
    echo 'echo "Running migrations..."' >> /start.sh && \
    echo 'php artisan migrate --force' >> /start.sh && \
    echo '' >> /start.sh && \
    echo 'echo "Creating storage link..."' >> /start.sh && \
    echo 'php artisan storage:link' >> /start.sh && \
    echo '' >> /start.sh && \
    echo 'echo "Setting final permissions..."' >> /start.sh && \
    echo 'chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo 'chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache' >> /start.sh && \
    echo '' >> /start.sh && \
    echo 'echo "Optimizing..."' >> /start.sh && \
    echo 'php artisan optimize:clear' >> /start.sh && \
    echo 'php artisan config:cache' >> /start.sh && \
    echo 'php artisan route:cache' >> /start.sh && \
    echo 'php artisan view:cache' >> /start.sh && \
    echo '' >> /start.sh && \
    echo 'echo "Starting PHP-FPM..."' >> /start.sh && \
    echo 'php-fpm -D' >> /start.sh && \
    echo '' >> /start.sh && \
    echo 'echo "Starting Nginx..."' >> /start.sh && \
    echo 'nginx -g "daemon off;"' >> /start.sh

RUN chmod +x /start.sh

CMD ["/start.sh"]