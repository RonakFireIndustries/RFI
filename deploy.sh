#!/bin/bash
set -e

REPO_DIR="$HOME/RFI"
BACKEND_WEBROOT="$HOME/rfibackend.ronakfire.com"

echo "=== Pulling latest code ==="
cd "$REPO_DIR" && git pull origin main

echo "=== Deploying Backend ==="
rsync -a --delete \
  --exclude='.env' \
  --exclude='vendor/' \
  --exclude='storage/' \
  --exclude='bootstrap/cache/' \
  --exclude='node_modules/' \
  "$REPO_DIR/backend/" "$BACKEND_WEBROOT/"

cd "$BACKEND_WEBROOT"
/opt/cpanel/composer/bin/composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
php artisan optimize

echo "=== Done ==="
