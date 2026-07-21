FROM php:8.3-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache Rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project
COPY . .

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader

# Set Apache DocumentRoot to public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
/etc/apache2/sites-available/*.conf \
/etc/apache2/apache2.conf \
/etc/apache2/conf-available/*.conf

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80

CMD php artisan migrate --force && apache2-foreground