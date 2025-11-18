# PHP mới nhất (Apache)
FROM php:8.3-apache

# Cài các thư viện để xử lý QR, barcode, Excel
RUN apt-get update && \
    apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libzip-dev \
        zip \
        libonig-dev \
        libxml2-dev \
        libicu-dev \
        imagemagick \
        libmagickwand-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        zip \
        intl \
        mbstring \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Bật mod_rewrite
RUN a2enmod rewrite

# Cấu hình VirtualHost cho qrson.test
RUN echo "<VirtualHost *:80>\n\
    ServerName qrson.test\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>" > /etc/apache2/sites-available/qrson.conf \
    && a2ensite qrson.conf \
    && a2dissite 000-default.conf

# Thư mục làm việc
WORKDIR /var/www/html

# Expose port Apache
EXPOSE 80
