# InfinityFree update workflow

The production source of truth is main. Keep the release manifest for every
successful deployment and create a Git tag such as
infinityfree-prod-20260904-1.

## Application code only

This includes controllers, views, routes, and frontend source when the
dependency lockfiles are unchanged:

1. Merge tested changes to main.
2. Back up the production database and runtime uploads.
3. Run php scripts/infinityfree/build-release.php.
4. Upload changed runtime files and the complete public/build/ directory when
   frontend code changed.
5. Do not overwrite .env, storage/app/private/uploads, runtime sessions,
   cache, or logs.
6. Smoke test the site and create a new production tag.

## Composer changes

If composer.json or composer.lock changed, set FULL_VENDOR_REQUIRED=YES:
rebuild staging vendor, repeat the PHP 1 MB audit, and upload the new vendor/
directory with the application changes.

## Database migration changes

If database/migrations changed, set DB_PATCH_REQUIRED=YES. Test the migrations
against an isolated temporary MySQL database, create and inspect an SQL patch,
back up production, import the patch with phpMyAdmin, deploy the matching
code, and smoke test. Never run destructive SQL automatically.

## Deleted files

Review git diff --name-status and manually delete files that were removed from
the application. Do not use a broad mirror/delete operation against htdocs:
production contains .env, user uploads, and runtime sessions/cache/logs that
must survive an application update.

The optional helper php scripts/infinityfree/build-update.php --from
<production-tag> can prepare an update directory and reports whether the
frontend, Composer, or migrations changed. It never copies .env or uploads
and never deletes remote files.
