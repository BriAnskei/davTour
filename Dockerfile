FROM php:8.3-fpm-bookworm

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
    dnsutils \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mbstring zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Setup Laravel
WORKDIR /var/www/html
COPY . /var/www/html

# CRITICAL: Remove any local .env that might have been copied
RUN rm -f .env .env.production

# Configure PHP-FPM
# Ensure clear_env = no is set so Render variables are visible to PHP
RUN sed -i 's/listen = .*/listen = 9000/' /usr/local/etc/php-fpm.d/www.conf && \
    if grep -q "clear_env" /usr/local/etc/php-fpm.d/www.conf; then \
        sed -i 's/;*clear_env = .*/clear_env = no/' /usr/local/etc/php-fpm.d/www.conf; \
    else \
        echo "clear_env = no" >> /usr/local/etc/php-fpm.d/www.conf; \
    fi

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

# Do NOT cache config at build time to avoid baking in empty/wrong values
RUN php artisan route:cache || true \
    && php artisan view:cache || true

EXPOSE 10000

# Create startup script
RUN echo "#!/bin/bash" > /start.sh && \
    echo "echo '=== Starting Laravel on Render ==='" >> /start.sh && \
    echo "echo 'Environment: \${APP_ENV:-production}'" >> /start.sh && \
    echo "echo 'App URL: \${APP_URL}'" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Debugging DNS...'" >> /start.sh && \
    echo "nslookup \${DB_HOST} || echo 'DNS lookup failed for \${DB_HOST}'" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Running migrations...'" >> /start.sh && \
    echo "php artisan migrate --force" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Creating storage link...'" >> /start.sh && \
    echo "php artisan storage:link --force || true" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Optimizing and Caching...'" >> /start.sh && \
    echo "php artisan optimize:clear" >> /start.sh && \
    echo "php artisan config:cache" >> /start.sh && \
    echo "php artisan route:cache" >> /start.sh && \
    echo "php artisan view:cache" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Finalizing permissions...'" >> /start.sh && \
    echo "chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Starting PHP-FPM...'" >> /start.sh && \
    echo "php-fpm -D" >> /start.sh && \
    echo "" >> /start.sh && \
    echo "echo 'Starting Nginx...'" >> /start.sh && \
    echo "nginx -g 'daemon off;'" >> /start.sh

RUN chmod +x /start.sh

CMD ["/start.sh"]
