#!/bin/bash
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
apache2-foreground
