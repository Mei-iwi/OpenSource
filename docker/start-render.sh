#!/bin/sh
set -e

upload_root="${UPLOAD_STORAGE_PATH:-/var/data/uploads}"
mkdir -p "$upload_root/avatars" "$upload_root/attendance-proofs"
chown -R www-data:www-data "$upload_root"

php artisan config:clear
php artisan view:clear
php artisan migrate --force
php artisan cache:clear

if [ -z "${APP_KEY:-}" ] && [ "${APP_ENV:-production}" = "local" ]; then
    key_file="storage/framework/docker-app-key"
    if [ ! -s "$key_file" ]; then
        php artisan key:generate --show > "$key_file"
        chmod 600 "$key_file"
    fi
    APP_KEY="$(cat "$key_file")"
    export APP_KEY
fi

if [ "${RUN_SEED:-false}" = "true" ]; then
    php artisan db:seed --force
fi

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
