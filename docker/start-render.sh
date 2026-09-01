#!/bin/sh
set -e

php artisan optimize:clear
php artisan migrate --force

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
