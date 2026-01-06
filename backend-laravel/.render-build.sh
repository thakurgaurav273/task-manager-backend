#!/bin/bash
# Render build script for Laravel

# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key if not set
php artisan key:generate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations (optional - can be done in release phase)
# php artisan migrate --force