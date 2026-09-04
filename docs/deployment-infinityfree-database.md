# InfinityFree database deployment

InfinityFree cannot run php artisan migrate on the server. Prepare SQL in a
separate local MySQL database, inspect it, and import it with phpMyAdmin.
Never use hr_management or hr_management_testing for an export unless the
owner explicitly confirms that choice. The preferred isolated database name
is hr_management_infinityfree_export.

## Mode A: schema only (default)

1. Create the empty temporary database hr_management_infinityfree_export.
2. Point a temporary local environment at that database. Keep the normal
   development .env unchanged. For example, set these values in a shell-local
   environment or a separate untracked .env:

       DB_CONNECTION=mysql
       DB_HOST=127.0.0.1
       DB_PORT=3306
       DB_DATABASE=hr_management_infinityfree_export
       DB_USERNAME=...
       DB_PASSWORD=...

3. Run php artisan migrate while that temporary environment is active.
4. Create a structure-only dump with mysqldump, if available:

       mysqldump --no-data --skip-comments --skip-add-drop-table ^
         --result-file=dist/infinityfree/database/schema.sql ^
         hr_management_infinityfree_export

   On a Unix-like shell, replace the caret line continuations with
   backslashes.
5. Inspect the SQL. It must not contain CREATE DATABASE, unrelated DROP
   DATABASE, application data, credentials, or secrets.
6. Import schema.sql into the InfinityFree database using phpMyAdmin.

If local MySQL credentials, database creation, or mysqldump are unavailable,
do not fabricate an SQL file. Create the temporary database and dump manually,
then inspect it before import.

## Mode B: schema plus demo data

Use this only when a demo dataset is explicitly wanted. Run the seeder only
against the isolated export database. The repository seeder retains its local
educational defaults, but the production export must not depend on publishing
those defaults.

Provide a private password locally through INFINITYFREE_DEMO_PASSWORD; do not
put its value in source, SQL examples, Git, or commit-oriented logs. Before
the dump, update every demo user password in the temporary database using
that environment value, verify the accounts, and then create a data dump:

       php artisan db:seed
       mysqldump --skip-comments --skip-add-drop-table ^
         --result-file=dist/infinityfree/database/demo.sql ^
         hr_management_infinityfree_export

The generated demo.sql is an artifact for a specific private deployment only.
Review it for real credentials and unrelated data before importing. If
INFINITYFREE_DEMO_PASSWORD is missing, stop and do not produce a demo dump.
Never run these commands against the normal development or testing database.

## Migration patches for later releases

When database/migrations changes, test the migration from the current
production schema in a fresh isolated temporary database. Produce an
InfinityFree-compatible SQL patch, back up production first, import the patch
with phpMyAdmin, and deploy the matching application code. Never use
migrate:fresh against a shared database and never auto-run destructive SQL.
