STEP: 10
PROJECT_STATUS: Hoàn thành AJAX availability, avatar upload và security polish; chưa triển khai Prompt 10.
LARAVEL_VERSION: 12.65.0
PHP_VERSION: Local 8.3.30; CI PHP 8.3.
CSS_STACK: Tailwind CSS 4 + Vite, giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade; Authentication, account.active và role middleware giữ nguyên.
DATABASE_STATUS: Runtime MySQL 8.4.3 hr_management; test MySQL hr_management_testing tại 127.0.0.1:3306.

AJAX_ENDPOINT: GET /hr/employees/check-code, named hr.employees.check-code; JSON available/message; Admin/HR only; server-side validation và unique rule vẫn bắt buộc.
AVATAR_UPLOAD: HR/Admin CRUD Employee và Employee self-service profile hỗ trợ JPG/JPEG/PNG/WEBP, tối đa 2 MB; tên path do Storage sinh, không tin filename client.
STORAGE: public disk; đã tạo public/storage link; avatar_path lưu trong employees; avatar cũ được xóa sau update thành công, file mới xóa khi update thất bại; runtime uploads không commit.
SECURITY_CHECKS: Form Request upload/availability, role authorization, ownership, CSRF, mass assignment, escaped Blade output, protected Employee fields và IDOR checks.

FILES_CHANGED:
- app/Http/Controllers/HR/EmployeeController.php
- app/Http/Controllers/Employee/ProfileController.php
- app/Http/Requests/EmployeeCodeAvailabilityRequest.php
- app/Http/Requests/StoreEmployeeRequest.php
- app/Http/Requests/UpdateEmployeeRequest.php
- app/Http/Requests/EmployeeProfileUpdateRequest.php
- resources/views/hr/employees/_form.blade.php
- resources/views/hr/employees/create.blade.php
- resources/views/hr/employees/edit.blade.php
- resources/views/hr/employees/show.blade.php
- resources/views/employee/profile/edit.blade.php
- resources/views/employee/profile/show.blade.php
- routes/web.php
- tests/Feature/SecurityPolishTest.php
- docs/codex-state.md

COMMANDS_RUN:
- php artisan make:request EmployeeCodeAvailabilityRequest
- php artisan storage:link
- composer validate
- php artisan route:list
- php artisan optimize:clear
- php artisan view:cache
- cmd /c npm run build
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test --filter=SecurityPolishTest
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test
- git status --short; git diff --stat

TEST_RESULTS: SecurityPolishTest 3 passed, 18 assertions; toàn bộ Feature Tests MySQL 60 passed, 218 assertions; route:list 61 routes; view:cache PASS.
BUILD_RESULTS: composer validate PASS; npm run build PASS; public/storage link PASS.
OPEN_ISSUES: Git còn thay đổi Prompt 09 chưa commit; Composer autoload warning cũ không làm fail kiểm tra; Prompt 10 chưa triển khai.
NEXT_STEP: 11_UI_RUBRIC
PROMPT_11_STEP: Prompt 11 - UI Polish & Rubric Evidence
UI_AUDIT: Shared layout, consistent navbar/sidebar, active role navigation, status badges, flash messages, empty states, responsive tables/forms and print CSS reviewed.
RESPONSIVE: Mobile menu opens through Alpine; desktop sidebar remains visible; content/tables use responsive Tailwind classes.
COMPONENTS: Reused page-header, status-badge, empty-state and shared flash components; no new frontend framework or package.
ROUTES_SMOKE_TESTED: All 61 routes listed successfully; existing runtime smoke tests pass without HTTP 500.
RUBRIC_EVIDENCE: docs/evidence/ui-rubric.md
SCREENSHOT_CHECKLIST: docs/evidence/screenshot-checklist.md; real screenshots remain pending and none were fabricated.
TEST_RESULTS_PROMPT_11: MySQL hr_management_testing; 61 tests, 222 assertions, 0 failures.
BUILD_RESULTS_PROMPT_11: composer validate PASS; optimize:clear PASS; route:list PASS; view:clear PASS; view:cache PASS; npm build PASS via cmd.exe.
FILES_CHANGED_PROMPT_11: resources/views/layouts/app.blade.php; resources/views/partials/navbar.blade.php; resources/views/partials/sidebar.blade.php; docs/evidence/ui-rubric.md; docs/evidence/screenshot-checklist.md; docs/codex-state.md
OPEN_ISSUES_PROMPT_11: Manual browser screenshots and GitHub Actions screenshot are pending collection; no runtime/test failures.
NEXT_STEP_PROMPT_11: Prompt 12

TEST_SUITE_STATUS: PASS
TOTAL_TESTS: 61
FAILURES: 0
TEST_DATABASE: MySQL hr_management_testing; khong fallback SQLite.
MANUAL_EVIDENCE: docs/test/manual-test-cases.md; docs/test/bug-log.md
PROMPT_10_FILES_CHANGED: docs/test/manual-test-cases.md; docs/test/bug-log.md; docs/codex-state.md
PROMPT_10_COMMANDS: composer validate; php artisan route:list; php artisan view:cache; php artisan test (MySQL hr_management_testing); cmd.exe /c npm run build
PROMPT_10_RESULTS: composer PASS; route:list PASS (61 routes); view:cache PASS; 61 tests/222 assertions PASS; frontend build PASS via cmd.exe.
PROMPT_10_NOTE: npm run build trực tiếp trong PowerShell bị chặn bởi chữ ký npm.ps1; chạy lại qua cmd.exe thành công. Manual mobile screenshots chưa được tạo.

ADDITIONAL_CHANGE: Bổ sung chức năng đăng xuất trên navbar dùng chung.
LOGOUT_STATUS: POST /logout qua Laravel Breeze; form có @csrf; controller invalidate session và regenerate token.
LOGOUT_TEST_RESULTS: AuthenticationTest kiểm tra logout backend và form logout hiển thị trong layout.
