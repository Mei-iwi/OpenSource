# Basic deployment guide

## Requirements

PHP >= 8.3, Composer, MySQL, a web server, and Node/npm for building assets.

## Install and build

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set `APP_ENV=production` and `APP_DEBUG=false`. Configure MySQL through environment variables; never place real credentials in source control.

## Production checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` configured
- [ ] Database credentials are not committed
- [ ] `.env` is not committed
- [ ] HTTPS enabled
- [ ] Storage permissions verified
- [ ] `public/storage` link exists
- [ ] Production migration backup exists
- [ ] Least-privilege database account used
- [ ] Demo accounts are not used in production

`migrate:fresh --seed` deletes existing data and is only for a confirmed development/demo database. Use production backups and a reviewed migration plan before deployment.
