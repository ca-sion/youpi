#!/bin/sh

# Turn on maintenance mode
echo 'Command: down'
php artisan down --secret="1630542a-246b-4b66-afa1-dd72a4c43515"

# Pull the latest changes from the git repository
# git reset --hard
# git clean -df
# git pull origin master

# Install/update composer dependecies
echo 'Command: composer'
composer install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader

# Run optimize command
php artisan optimize

# Run generate key
# echo 'Command: key'
# php artisan key:generate

# Run storage link
echo 'Command: storage'
php artisan storage:link

# Run database migrations with seed
# echo 'Command: migrate'
php artisan migrate --force --seed
# --force  Required to run when in production.

# Run database migrations
# echo 'Command: migrate'
# php artisan migrate --force
# --force  Required to run when in production.

# Run database migrations with a new database
# echo 'Command: migrate'
# php artisan migrate:fresh --seed --force

# Clear caches
echo 'Command: cache'
php artisan cache:clear

# Laravel clear expired password reset tokens
# php artisan auth:clear-resets

# Laravel clear and cache routes
php artisan route:cache

# Laravel clear and cache config
php artisan config:cache

# Laravel clear and cache views
php artisan view:cache

# Laravel clear and cache events
# php artisan event:cache

# Install node modules
# npm install

# Build assets using Laravel Mix
# npm run production

# Start Cronless schedule
# php artisan schedule:run-cronless

# Turn off maintenance mode
echo 'Command: up'
php artisan up

echo '🚀 Deploy finished.'
