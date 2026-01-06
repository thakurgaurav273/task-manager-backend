FROM php:8.4-cli-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files first (for better caching)
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application files
COPY . /var/www

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Create necessary directories and set permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && mkdir -p bootstrap/cache \
    && chmod -R 777 storage \
    && chmod -R 775 bootstrap/cache

# Note: Alpine doesn't have www-data user by default, create it
RUN addgroup -g 82 -S www-data && adduser -u 82 -D -S -G www-data www-data
RUN chown -R www-data:www-data /var/www

# Use PORT environment variable from Render
ENV PORT=8000

# Expose the port (will be overridden by Render's PORT env var)
EXPOSE $PORT

# Start PHP built-in server with dynamic port
CMD php artisan serve --host=0.0.0.0 --port=${PORT}

APP_NAME=Laravel
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com

DB_CONNECTION=mysql
DB_HOST=your-railway-mysql-host
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password

LOG_CHANNEL=stack
LOG_LEVEL=debug

SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync