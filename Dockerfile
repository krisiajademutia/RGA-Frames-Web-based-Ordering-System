FROM php:8.2-apache

# Install system dependencies needed for zip, MySQL, and GD
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install mysqli pdo pdo_mysql zip gd

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Install Composer automatically
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy your project files into the container
COPY . /var/www/html/

# Run composer install to pull down your packages
WORKDIR /var/www/html
RUN composer install --no-interaction --optimize-autoloader

EXPOSE 80
