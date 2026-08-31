STEP: Runtime/UI stabilization before Prompt 09
PROJECT_STATUS: Prompt 01-08 hoàn thành; runtime UI đã ổn định; chưa triển khai Prompt 09.
LARAVEL_VERSION: 12.65.0
PHP_VERSION: Local 8.3.30; CI PHP 8.3.
CSS_STACK: Tailwind CSS 4 + Vite, giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade; Authentication và account.active protection hoạt động.
ROLE_STATUS: Admin/HR và Employee routes được bảo vệ server-side; menu theo role.
DATABASE_STATUS: Runtime MySQL 8.4.3 hr_management; test MySQL hr_management_testing tại 127.0.0.1:3306.

ROOT_CAUSES:
- Preview routes render dashboard.hr/admin trực tiếp nhưng không truyền data từ dashboard controllers, gây Undefined variable và HTTP 500.
- Sidebar/navbar vẫn trỏ preview routes sau khi dashboard thật đã tích hợp.
- Dashboard report aggregate query kế thừa ORDER BY khi GROUP BY, đã xử lý trước bước ổn định này.

ROUTES_AUDITED:
- Public/auth: /, /login, /dashboard và auth routes.
- Admin: dashboard, users index/create/show/edit.
- HR: dashboard, departments, employees, attendances, reports, export.csv, print.
- Employee: dashboard, profile/show/edit, attendances.
- Preview routes cũ: /preview/admin, /preview/hr, /preview/employee.

PREVIEW_ROUTE_DECISION: Removed all 3 preview routes because real dashboards are integrated. No preview links remain in resources/routes.

FILES_CHANGED:
- routes/web.php
- resources/views/partials/sidebar.blade.php
- resources/views/partials/navbar.blade.php
- tests/Feature/RuntimeSmokeTest.php
- docs/evidence/runtime-smoke-test.md
- docs/codex-state.md

RUNTIME_FIXES:
- Dashboard thật tiếp tục nhận đủ dữ liệu từ controller; menu dùng route thật theo role.
- Smoke coverage cho populated GET pages, empty-safe preview removal và reports/CSV/print.
- Không sửa database/auth stack; không triển khai Prompt 09.

COMMANDS_RUN:
- git status --short; git branch --show-current
- php artisan route:list
- rg preview references
- composer validate
- php artisan optimize:clear
- php artisan view:clear
- php artisan view:cache
- cmd /c npm run build
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test --filter=RuntimeSmokeTest
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test

TEST_RESULTS: composer validate PASS; route:list PASS (60 routes); view:cache PASS; npm run build PASS; full Feature Tests PASS: 57 tests, 200 assertions.
SMOKE_TEST_RESULTS: 2 tests, 29 assertions PASS; all audited populated GET pages 200; removed preview URLs 404 as expected.
OPEN_ISSUES: Git còn thay đổi chưa commit của bước runtime stabilization; Composer autoload warning cũ không làm fail kiểm tra; Prompt 09 chưa triển khai.
NEXT_STEP: Prompt 09
