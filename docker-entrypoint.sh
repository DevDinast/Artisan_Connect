#!/bin/bash
php artisan migrate:fresh --force
php artisan db:seed --force
php artisan storage:link
apache2-foreground
