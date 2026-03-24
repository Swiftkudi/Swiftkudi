# Use the official PHP image with Apache
FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libpq-dev \
    nodejs \
    npm \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions including PostgreSQL
RUN docker-php-ext-install pdo_pgsql pgsql zip mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files first (for composer)
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy rest of application
COPY . .

# Install Node dependencies and build assets
RUN npm install && npm run production

# Create storage directories
RUN mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache

# Set ownership and permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Configure Apache
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    ErrorLog ${APACHE_LOG_DIR}/error.log\n\
    CustomLog ${APACHE_LOG_DIR}/access.log combined\n\
    </VirtualHost>' > /etc/apache2/sites-available/000-default.conf

# Create startup script
RUN printf '#!/bin/bash\n\
    set -e\n\
    echo "Setting permissions..."\n\
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache\n\
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache\n\
    echo "Generating APP_KEY if not set..."\n\
    if [ -z "$APP_KEY" ]; then\n\
    export APP_KEY=$(php artisan key:generate --show)\n\
    echo "APP_KEY generated"\n\
    fi\n\
    echo "Caching configuration..."\n\
    php artisan config:clear\n\
    php artisan config:cache\n\
    php artisan route:cache\n\
    php artisan view:cache\n\
    echo "Running migrations..."\n\
    php artisan migrate --force || echo "Migration skipped"\n\
    echo "Running database seeder..."\n\
    php artisan db:seed --force || echo "Seeder skipped"\n\
    echo "Starting Apache..."\n\
    exec apache2-foreground\n' > /start.sh && chmod +x /start.sh

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["/start.sh"]
