# Final project summary

## Project

Luna HR is a server-rendered website for managing users, departments, employees, attendance and basic HR reports. It is designed at student-project scope with conventional Laravel MVC.

## Technology

The project uses Laravel 12.65, PHP 8.3, Laravel Breeze Blade, MySQL 8.4, Blade, Tailwind/Vite, Alpine.js, JavaScript Fetch API, Composer, npm and GitHub Actions.

## Roles and modules

- Admin manages accounts, roles, account status and all HR areas.
- HR manages departments, employees, attendance and reports.
- Employee views their own profile and attendance and updates only permitted personal fields.
- Modules include authentication, user management, department/employee CRUD, attendance, self-service, reports/statistics, CSV, print, AJAX employee-code checking and avatar upload.

## Database

The main tables are `users`, `departments`, `employees` and `attendances`. Eloquent relations and unique/foreign-key constraints protect the data. Reports are calculated from existing records; there is no reports table.

## Security

Authentication uses Laravel Breeze session authentication. Middleware protects active accounts and roles. Form Requests, CSRF, policies/Gates, ownership checks, mass-assignment protection and avatar MIME/size validation are used. Blade output is escaped.

## Testing and CI

The verified regression suite uses MySQL `hr_management_testing` and has 61 tests, 222 assertions and 0 failures. GitHub Actions uses PHP 8.3, MySQL 8.4, `pdo_mysql`, `mbstring`, Composer install, `npm ci`, frontend build and tests.

## Results and limitations

The main application flows render without known HTTP 500 errors. Manual screenshots, final team contribution details and post-push CI confirmation remain human submission actions. The project does not include payroll, KPI, recruitment, notifications or cloud infrastructure.

## Future improvements

Possible future work includes leave-request workflows, payroll, richer charts, notifications and production cloud deployment. These are not current features.
