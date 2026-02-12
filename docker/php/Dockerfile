FROM php:7.4-fpm-alpine

# PHP 7.4 is required because FluxBB 1.5 does not support newer PHP versions.
# Most FluxBB installations run on PHP 7.4 or older.

# Install git, composer, and required PHP extensions
RUN apk add --no-cache git oniguruma-dev $PHPIZE_DEPS \
    && docker-php-ext-install mysqli mbstring \
    && pecl install pcov \
    && docker-php-ext-enable pcov

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www/html

# Create directories
RUN mkdir -p /var/www/html/output /var/www/html/input

# Copy entrypoint script
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
