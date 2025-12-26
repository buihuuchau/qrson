# =========================
# Base
# =========================
FROM php:8.3-apache

# =========================
# System dependencies tối thiểu
# =========================
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# =========================
# PHP extensions cho Laravel + PostgreSQL
# =========================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        mbstring \
        gd \
        opcache

# =========================
# Composer
# =========================
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =========================
# Apache config
# =========================
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# =========================
# Laravel VirtualHost (port cố định 80)
# =========================
RUN printf "<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/laravel.conf \
    && a2ensite laravel.conf \
    && a2dissite 000-default.conf

# =========================
# Workdir
# =========================
WORKDIR /var/www/html

# =========================
# Copy source code
# =========================
COPY . .

# =========================
# Install Laravel dependencies
# =========================
RUN composer install --no-dev --optimize-autoloader

# =========================
# Permissions
# =========================
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# =========================
# Start Apache
# =========================
CMD ["apache2-foreground"]
