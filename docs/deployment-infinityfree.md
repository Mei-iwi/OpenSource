# InfinityFree deployment

This project supports InfinityFree as an additional deployment target. Render
and Docker files remain in the repository. This document describes a manual
FTP/phpMyAdmin deployment; it does not deploy anything automatically.

## Repository audit findings

- The application is Laravel 12 on PHP 8.3 with Breeze Blade, Vite, Tailwind,
  and MySQL-compatible migrations.
- Avatar and attendance proof code writes to the private local
  persistent_uploads disk and streams through authenticated Laravel routes.
  No public-storage URL is required for these features.
- Local development uses database-backed sessions/cache in its existing
  environment. The InfinityFree template changes both to file storage.
- Queue work is synchronous, mail uses the log transport, and no active Redis,
  Reverb/Echo, S3/R2, or application cron integration was found in the
  audited runtime paths. The GitHub workflow's scheduled check is separate
  from hosting runtime.
- Blade layouts use Vite's build manifest. The release builder therefore
  requires public/build/manifest.json and excludes node_modules.
- Guest registration remains available by default for local development and is
  disabled only by PUBLIC_REGISTRATION_ENABLED=false in the production
  template.

## Before starting

1. Create an InfinityFree account and a free subdomain, or attach a domain
   already controlled by you.
2. Create a MySQL database in the InfinityFree control panel. Record the
   database host, database name, username, and password privately.
3. Create the FTP account shown by the control panel. These credentials are
   entered only into FileZilla and must not be placed in Git, tickets, or chat.
4. Build the release locally:

       php scripts/infinityfree/build-release.php

   The resulting upload root is dist/infinityfree/htdocs/. The builder runs
   npm ci, npm run build, installs production Composer dependencies in
   staging, removes local runtime state, audits PHP file sizes, and writes
   dist/infinityfree/release-manifest.json.

## Uploading the application

1. In FileZilla, connect with the InfinityFree FTP host, username, password,
   and port provided by the control panel.
2. Open the remote htdocs/ directory.
3. Upload the contents of dist/infinityfree/htdocs/ into htdocs/.
4. The file deploy/infinityfree/htdocs.htaccess is copied by the builder as
   the root htdocs/.htaccess. It routes requests to the application's
   public/ directory. Keep the existing public/.htaccess unchanged.
5. Do not upload .env, .env.production, local logs, local sessions, local
   cache, tests, node_modules, or development-only files.

The package contains vendor/ and compiled Vite files in public/build/;
InfinityFree is not expected to run Composer, npm, or Artisan. The package
also contains the application storage/ directories. Ensure these directories
are writable by the hosting account:

    storage/app/private/uploads
    storage/framework/cache/data
    storage/framework/sessions
    storage/framework/views
    storage/logs
    bootstrap/cache

Avatars and attendance proofs use the private persistent_uploads local disk.
They are served only through authenticated Laravel routes and do not require
php artisan storage:link or a public /storage rewrite.

## Creating the production environment

Copy .env.infinityfree.example to a new local file named .env only for
editing, replace every YOUR_INFINITYFREE_* placeholder with values from the
hosting panel, and upload that .env to the root of htdocs/. Never commit it
and never overwrite it during a later code-only update.

Generate an application key locally and paste the result into that server
.env:

    php artisan key:generate --show

The template deliberately uses CACHE_STORE=file, SESSION_DRIVER=file,
QUEUE_CONNECTION=sync, and MAIL_MAILER=log because the target has no Redis,
worker, cron, or required mail service. APP_DEBUG=false and
PUBLIC_REGISTRATION_ENABLED=false are intentional production settings. Do not
set UPLOAD_STORAGE_PATH unless a verified absolute hosting path is
intentionally required; the application falls back to
storage/app/private/uploads when it is absent or blank.

## Database and first smoke test

Follow the database procedure in deployment-infinityfree-database.md to
prepare a schema-only SQL export, then import it with the InfinityFree
phpMyAdmin interface. Do not import a development dump.

After importing the schema and uploading .env, check:

- / and /login load without a server error.
- /register is unavailable.
- Login works for the accounts intentionally imported into the database.
- A permitted user can view an avatar and attendance proof.
- An employee cannot view another employee's attendance proof.
- Uploading a new avatar/proof persists after a fresh request.

Security checks after deployment:

    GET /.env
    GET /storage/logs/laravel.log
    GET /vendor/autoload.php
    GET /config/app.php

These must not reveal environment values, logs, or PHP source. A normal
404/403 response is expected.

## Backups and updates

Export a phpMyAdmin backup before every database change and retain a copy of
the exact release manifest. Create a Git tag after each successful deployment,
for example infinityfree-prod-20260904-1.

For application-only updates, merge tested code to main, rebuild the package,
back up production, and upload changed runtime files. Upload the complete
public/build/ directory when frontend files changed. Never overwrite .env or
storage/app/private/uploads.

For Composer or database changes, follow the update workflow in
deployment-infinityfree-update.md. Database patches must be tested against
an isolated temporary database and imported manually through phpMyAdmin;
destructive SQL is never run automatically.
