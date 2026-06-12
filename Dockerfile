<<<<<<< HEAD
FROM php:7.4-apache
=======
﻿FROM php:7.4-apache
>>>>>>> 656a0d0 (Fix Dockerfile (remove nproc), update database config for MySQL, and update railway.json)

# Configure apt retries
RUN echo 'APT::Acquire::Retries "5";' > /etc/apt/apt.conf.d/80-retries \\
    && echo 'APT::Get::Fix-Missing "true";' >> /etc/apt/apt.conf.d/80-retries

# Install dependencies with retry logic
RUN apt-get update && \\
    apt-get install -y --no-install-recommends --fix-missing \\
        libonig-dev \\
        libzip-dev \\
        libpng-dev \\
        libjpeg-dev \\
        libfreetype6-dev \\
        libpq-dev \\
    || (echo "Retrying installation..." && sleep 10 && \\
        apt-get install -y --no-install-recommends --fix-missing \\
            libonig-dev \\
            libzip-dev \\
            libpng-dev \\
            libjpeg-dev \\
            libfreetype6-dev \\
            libpq-dev) \\
    && apt-get clean \\
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (removed -j for Windows compatibility)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \\
    && docker-php-ext-install gd mbstring zip pdo_mysql pdo_pgsql opcache

# Enable Apache modules
RUN a2enmod rewrite headers

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install dependencies
RUN composer install --no-interaction --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html \\
    && chmod -R 755 /var/www/html/storage \\
    && chmod -R 755 /var/www/html/bootstrap/cache

# Generate app key
RUN php artisan key:generate --force

EXPOSE 80

CMD ["apache2-foreground"]
