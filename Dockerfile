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
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# Enable mod_rewrite for Laravel
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . /app

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache
RUN chmod -R 775 /app/storage /app/bootstrap/cache

# Configure Apache to serve Laravel's public directory
RUN echo '<Directory /app/public>\n\
    Options -MultiViews\n\
    AllowOverride All\n\
    <IfModule mod_rewrite.c>\n\
        RewriteEngine On\n\
        RewriteCond %{REQUEST_FILENAME} !-f\n\
        RewriteRule ^ index.php [QSA,L]\n\
    </IfModule>\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf && \
    a2enconf laravel

# Set document root
ENV APACHE_DOCUMENT_ROOT=/app/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf /etc/apache2/mods-available/dir.load

# Set environment
ENV APP_ENV=production
ENV APP_DEBUG=false

# Create startup script
RUN echo '#!/bin/bash\n\
set -e\n\
echo "Running migrations..."\n\
php artisan migrate --force\n\
echo "Starting Apache..."\n\
exec apache2-foreground' > /app/startup.sh && \
    chmod +x /app/startup.sh

EXPOSE 80

CMD ["/app/startup.sh"]