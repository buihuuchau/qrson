# =========================
# Base image
# =========================
FROM php:8.3-apache

# =========================
# System dependencies
# =========================
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libpq-dev \
    pkg-config \
    libwebp-dev \
    imagemagick \
    libmagickwand-dev \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

# =========================
# PHP extensions (GD + PostgreSQL + Laravel cần) 
# =========================
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        mbstring \
        opcache

# =========================
# Imagick
# =========================
RUN pecl install imagick \
    && docker-php-ext-enable imagick

# =========================
# Composer
# =========================
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer

# =========================
# Apache config
# =========================
RUN a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# =========================
# VirtualHost for Laravel (port cố định 80)
# =========================
RUN echo "<VirtualHost *:80>\n\
    ServerName qrson.test\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    ErrorLog \${APACHE_LOG_DIR}/error.log\n\
    CustomLog \${APACHE_LOG_DIR}/access.log combined\n\
</VirtualHost>" > /etc/apache2/sites-available/qrson.conf \
    && a2ensite qrson.conf \
    && a2dissite 000-default.conf

# =========================
# OPcache
# =========================
RUN echo "opcache.enable=1\n\
opcache.enable_cli=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=20000\n\
opcache.validate_timestamps=1\n\
opcache.revalidate_freq=2\n\
opcache.fast_shutdown=1" \
> /usr/local/etc/php/conf.d/opcache.ini

# =========================
# Workdir
# =========================
WORKDIR /var/www/html

# =========================
# Copy source code
# =========================
COPY . /var/www/html

# =========================
# Install Laravel dependencies
# =========================
RUN composer install --no-dev --optimize-autoloader

# =========================
# Permissions
# =========================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# =========================
# Expose & start Apache
# =========================
EXPOSE 80
CMD ["apache2-foreground"]
