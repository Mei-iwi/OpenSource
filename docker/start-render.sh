#!/bin/sh
set -e

upload_root="${UPLOAD_STORAGE_PATH:-/var/data/uploads}"
mkdir -p "$upload_root/avatars" "$upload_root/attendance-proofs"
chown -R www-data:www-data "$upload_root"

php artisan optimize:clear
php artisan migrate --force

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
