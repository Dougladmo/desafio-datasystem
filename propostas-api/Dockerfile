FROM php:8.2-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libpq \
    git \
    unzip \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql

# Install Redis extension
RUN apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Set permissions
RUN chmod -R 777 writable/

# Expose port
EXPOSE 8080

CMD ["php", "spark", "serve", "--host=0.0.0.0", "--port=8080"]
