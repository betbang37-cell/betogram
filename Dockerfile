FROM php:7.4-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libsqlite3-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install PHP dependencies (ignore Redis if not available)
RUN composer install --no-interaction --optimize-autoloader --no-dev --ignore-platform-req=ext-redis

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Apache document root to Laravel's public folder
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/mods-available/dir.load

# Set environment
ENV APP_ENV=production
ENV APP_DEBUG=false

EXPOSE 80

# Create and use startup script with migrations
RUN echo '#!/bin/bash\n\
set -e\n\
echo "Running migrations..."\n\
php artisan migrate --force\n\
echo "Starting Apache..."\n\
exec apache2-foreground' > /usr/local/bin/startup.sh && \
    chmod +x /usr/local/bin/startup.sh

CMD ["/usr/local/bin/startup.sh"]