#!/bin/sh
set -eu

mkdir -p \
    storage/app/private/uploads \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    key_file="storage/framework/docker-app-key"
    if [ ! -s "$key_file" ]; then
        php artisan key:generate --show --no-ansi > "$key_file"
        chmod 600 "$key_file"
    fi
    APP_KEY="$(cat "$key_file")"
    export APP_KEY
fi

echo "Waiting for MySQL at ${DB_HOST:-db}:${DB_PORT:-3306}..."
until php -r '
    try {
        new PDO(
            "mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"),
            getenv("DB_USERNAME"),
            getenv("DB_PASSWORD")
        );
    } catch (Throwable $exception) {
        exit(1);
    }
'; do
    sleep 2
done

php artisan config:clear
php artisan view:clear
php artisan migrate --force
php artisan cache:clear

exec php artisan serve --host=0.0.0.0 --port=8000
