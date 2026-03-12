#!/bin/bash

# EarnDesk Production Build Script
# Run this script before deploying to production

set -e

echo "🚀 Building EarnDesk for production..."

# Clear all caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies and build assets
echo "📦 Building frontend assets..."
npm install
npm run production

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan storage:link --quiet 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

echo "✅ Build complete!"
echo ""
echo "Next steps:"
echo "1. Set your environment variables in Render"
echo "2. Push your code to GitHub"
echo "3. Deploy on Render"
