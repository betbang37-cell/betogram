FROM php:7.4-apache

# Install system dependencies with retry logic
RUN apt-get update && \
    for i in 1 2 3; do \
      apt-get install -y \
        git \
        curl \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
        libsqlite3-dev \
        pkg-config \
        zip \
        unzip && break || ([ $i -lt 3 ] && sleep 10); \
    done && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite pdo_pgsql mbstring exif pcntl bcmath gd

# Disable ALL MPM modules at build time
RUN rm -f /etc/apache2/mods-enabled/mpm_* /etc/apache2/mods-available/mpm_worker* /etc/apache2/mods-available/mpm_event* && \
    a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Configure Apache - replace default config with proper Laravel setup
RUN rm /etc/apache2/sites-enabled/000-default.conf && \
    echo '<VirtualHost *:80>' > /etc/apache2/sites-available/000-default.conf && \
    echo '  ServerName localhost' >> /etc/apache2/sites-available/000-default.conf && \
    echo '  DocumentRoot /var/www/html/public' >> /etc/apache2/sites-available/000-default.conf && \
    echo '  <Directory /var/www/html/public>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Options -MultiViews +FollowSymLinks' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    AllowOverride All' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '  </Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</VirtualHost>' >> /etc/apache2/sites-available/000-default.conf && \
    a2ensite 000-default.conf

# Create empty SQLite database file
RUN touch /var/www/html/database/database.sqlite && \
    chown www-data:www-data /var/www/html/database/database.sqlite

# Create entrypoint script that cleans up MPM modules at runtime
RUN echo '#!/bin/bash\nset -e\nrm -f /etc/apache2/mods-enabled/mpm_worker* /etc/apache2/mods-enabled/mpm_event* 2>/dev/null || true\nexec apache2-foreground' > /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
