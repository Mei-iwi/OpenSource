# Website Quan ly Nhan su

## 1. Gioi thieu

A server-rendered human resources management website for a student open-source programming project.

## 2. Muc tieu

Provide a clear workflow for account management, departments, employees, attendance and basic reports.

## 3. Chuc nang

- Login, logout, password and profile management.
- Admin user management: create HR/Employee accounts, search, filter, pagination, role and account lock/unlock.
- Department and Employee CRUD for Admin/HR.
- Attendance management for Admin/HR and self-service history for Employee.
- Admin/HR dashboards, statistics, filtered CSV export and browser print view.
- Employee-code availability via Fetch/JSON and avatar upload via public Storage.

## 4. Roles

- `admin`: full system access, including user and role management.
- `hr`: manages departments, employees, attendance and reports; cannot manage the admin role.
- `employee`: views and updates permitted personal fields and sees only their own attendance.

## 5. Cong nghe

PHP 8.3, Laravel 12.65, Laravel Breeze Blade, MySQL 8.4, Blade, Tailwind CSS, Vite, Alpine.js, JavaScript/Fetch API, Composer, npm, Git and GitHub Actions.

## 6. Yeu cau moi truong

- PHP 8.3+
- Composer
- MySQL 8.4 (or compatible MySQL)
- Node.js and npm for frontend build
- Web server or PHP Artisan server

## 7. Cai dat

```bash
git clone <repository-url>
cd project
composer install
npm install
cp .env.example .env
php artisan key:generate
```

On Windows, use `Copy-Item .env.example .env` instead of `cp`.

## 8. Database

Configure `.env` with a development MySQL database:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_management
DB_USERNAME=root
DB_PASSWORD=
```

Run `php artisan migrate --seed` and, once per installation, `php artisan storage:link`. The project uses `users`, `departments`, `employees` and `attendances`; there is no `reports` table.

## 9. Seed demo data

`DatabaseSeeder` creates 1 Admin, 2 HR users, 12 Employee users, 4 departments and attendance records for the recent 45 days. Demo password is fixed by the Seeder and is for development/demo only:

| Role | Email | Password | Purpose |
|---|---|---|---|
| Admin | `admin@example.com` | `Password123!` | System administration |
| HR | `hr@example.com` | `Password123!` | HR management |
| HR | `hr2@example.com` | `Password123!` | HR management |
| Employee | `employee1@example.com` ... `employee12@example.com` | `Password123!` | Self-service |

Do not use demo credentials in production.

## 10. Chay ung dung

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Open `http://127.0.0.1:8000`.

## 11. Chay tests

Tests use MySQL database `hr_management_testing`, not SQLite:

```bash
php artisan test
```

The latest verified suite has 61 tests, 222 assertions and 0 failures.

## 12. GitHub Actions

`.github/workflows/tests.yml` runs PHP 8.3, MySQL 8.4, Composer install, `npm ci`, `npm run build` and Feature Tests against `hr_management_testing`.

## 13. Cau truc project

Key directories are `app/Http/Controllers`, `app/Http/Requests`, `app/Models`, `app/Policies`, `database/migrations`, `database/factories`, `database/seeders`, `resources/views`, `routes`, `tests/Feature` and `docs`.

## 14. Tai lieu

See `docs/architecture.md`, `docs/diagrams/erd.md`, `docs/report-outline.md`, `docs/requirements-matrix.md`, `docs/test/`, `docs/evidence/` and `docs/deployment.md`.

## 15. Deployment

See `docs/deployment.md` for the basic production checklist. Never commit `.env`, real passwords, tokens or production credentials.

`php artisan migrate:fresh --seed` deletes existing database data and must only be used with an explicitly confirmed development/demo database.
