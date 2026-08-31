STEP: 05
PROJECT_STATUS: Đã hoàn thành quản trị tài khoản Admin; chưa triển khai CRUD Department/Employee/Attendance.
LARAVEL_VERSION: 12.65.0
CSS_STACK: Tailwind CSS 4 + Vite scaffold hiện có; giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade đã cài; login/logout/register/password flows hoạt động.
ROLE_STATUS: Đã có admin/hr/employee, User helpers, RoleMiddleware và AccountActiveMiddleware; kiểm tra server-side.
DATABASE_STATUS: Runtime development dùng MySQL 8.4.3 tại 127.0.0.1:3306, database `hr_management`; `.env` không commit.
SEED_STATUS: Đã seed thành công dữ liệu demo vào `hr_management`.
COMPLETED:
- Đọc `docs/codex-state.md`, toàn bộ `codex-prompts/00_QUY_TAC_CHUNG_LUNA56.txt` và `codex-prompts/05_ADMIN_USER_ROLE_MANAGEMENT.txt`.
- Tạo `Admin\\UserController` resource với danh sách, search/filter/pagination, show/create/update/delete có bảo vệ user gắn Employee.
- Tạo Store/Update Form Request với validation email, role, password và account_status.
- Thêm route resource `admin.users.*` và PATCH lock/unlock, bảo vệ bằng auth + account.active + role:admin.
- Hoàn thiện UI Blade danh sách/form/chi tiết tài khoản, badge, validation error, CSRF/method spoofing và confirm lock/unlock.
- Bảo vệ server-side: chỉ Admin truy cập; chỉ tạo HR/Employee; không tự hạ quyền/khóa/xóa Admin đang đăng nhập.
- Thêm Feature Tests cho Admin user management.
- Đọc `docs/codex-state.md`, toàn bộ `codex-prompts/00_QUY_TAC_CHUNG_LUNA56.txt` và `codex-prompts/04_DATABASE_ELOQUENT_MIGRATION_SEEDER_FACTORY.txt`.
- Xác nhận MySQL Laragon hoạt động trên port 3306; tạo database development `hr_management` và database test riêng `hr_management_testing`.
- Cấu hình `.env` dùng MySQL; `.env.example` có mẫu MySQL không chứa mật khẩu thật.
- Tạo migrations cho departments, employees, attendances với foreign keys, restrict delete và unique constraints.
- Tạo models/relations/fillable/casts cho User, Department, Employee, Attendance.
- Tạo factories và deterministic DatabaseSeeder: 1 admin, 2 HR, 12 employee, 4 department, 540 attendance.
- Tạo ERD Mermaid và tài liệu evidence database.
- Chạy migration/seeding trên database dev đã xác nhận an toàn để reset.
- Đọc `docs/codex-state.md`, toàn bộ `codex-prompts/00_QUY_TAC_CHUNG_LUNA56.txt` và `codex-prompts/03_AUTH_3_ROLE_MIDDLEWARE_POLICY.txt`.
- Cài Laravel Breeze Blade; giữ Tailwind CSS 4 + Vite và bảo toàn UI skeleton Prompt 02.
- Thêm migration `role`/`account_status`, User role helpers, middleware alias và dashboard controllers/routes theo role.
- Chặn locked account khi login và trên protected routes.
- Thêm feature tests cho guest, 3 role, server-side authorization và locked account.
- Đọc `docs/codex-state.md` và toàn bộ `codex-prompts/00_QUY_TAC_CHUNG_LUNA56.txt`, `codex-prompts/02_UI_BLADE_3_ROLE_DESIGN.txt`.
- Tạo Blade layout responsive, navbar, sidebar, flash partial và các component page-header/status-badge/empty-state.
- Tạo view khung cho dashboard 3 role, tài khoản Admin, phòng ban/nhân viên/chấm công/báo cáo HR và hồ sơ/lịch sử chấm công Employee.
- Thêm route preview tạm `/preview/admin`, `/preview/hr`, `/preview/employee` có TODO REMOVE AFTER INTEGRATION.
- Đọc/audit repository hiện tại và xác nhận Laravel 12.
- Kiểm tra PHP, Composer, Artisan, Git, composer.json, routes/web.php, migrations, models, views, README và .gitignore.
- Tạo/chuẩn hóa cấu trúc thư mục tài liệu cho roadmap.
FILES_CHANGED:
- docs/codex-state.md
- routes/web.php
- resources/views/layouts/app.blade.php
- resources/views/partials/navbar.blade.php
- resources/views/partials/sidebar.blade.php
- resources/views/partials/flash.blade.php
- resources/views/components/page-header.blade.php
- resources/views/components/status-badge.blade.php
- resources/views/components/empty-state.blade.php
- resources/views/dashboard/*.blade.php
- resources/views/admin/users/index.blade.php
- resources/views/hr/**/*.blade.php
- resources/views/employee/**/*.blade.php
- composer.json
- composer.lock
- package.json
- package-lock.json
- resources/css/app.css
- resources/js/app.js
- vite.config.js
- postcss.config.js
- tailwind.config.js
- routes/auth.php
- app/Http/Controllers/Auth/*
- app/Http/Controllers/*DashboardController.php
- app/Http/Middleware/RoleMiddleware.php
- app/Http/Middleware/AccountActiveMiddleware.php
- app/Http/Requests/Auth/*
- app/Models/User.php
- bootstrap/app.php
- database/migrations/*add_role_and_account_status_to_users_table.php
- database/factories/UserFactory.php
- tests/Feature/Auth/*
- tests/Feature/RoleAuthorizationTest.php
- database/migrations/2026_08_31_105136_create_departments_table.php
- database/migrations/2026_08_31_105137_create_employees_table.php
- database/migrations/2026_08_31_105138_create_attendances_table.php
- app/Models/Department.php
- app/Models/Employee.php
- app/Models/Attendance.php
- database/factories/DepartmentFactory.php
- database/factories/EmployeeFactory.php
- database/factories/AttendanceFactory.php
- database/seeders/DatabaseSeeder.php
- docs/diagrams/erd.md
- docs/evidence/database.md
- tests/Feature/DatabaseSchemaTest.php
- app/Http/Controllers/Admin/UserController.php
- app/Http/Requests/StoreUserRequest.php
- app/Http/Requests/UpdateUserRequest.php
- resources/views/admin/users/index.blade.php
- resources/views/admin/users/_form.blade.php
- resources/views/admin/users/create.blade.php
- resources/views/admin/users/edit.blade.php
- resources/views/admin/users/show.blade.php
- tests/Feature/AdminUserManagementTest.php
- .env (ignored, not committed)
- .env.example
COMMANDS_RUN:
- git status --short
- php -v
- composer -V
- php artisan --version
- php artisan about
- composer validate
 - php artisan route:list
 - php artisan view:clear
 - php artisan view:cache
 - php artisan serve --host=127.0.0.1 --port=8099
 - curl preview admin/hr/employee
 - php artisan migrate --force
 - php artisan migrate:status
 - npm install
 - npm run build
- set DB_CONNECTION=sqlite && set DB_DATABASE=:memory: && php artisan test --testsuite=Feature
- mysql connection/database checks
- php artisan config:clear
- php artisan migrate:fresh --seed --force
- php artisan migrate:status
- mysql seed count/relation/duplicate checks
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing && php artisan test
- php artisan route:list
- php artisan view:cache
- composer validate
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing && php artisan test
TEST_RESULTS: `route:list`, `view:cache`, `composer validate` đạt; toàn bộ Feature Tests trên MySQL test DB đạt 39 tests, 98 assertions; Admin user management tests đạt.
OPEN_ISSUES:
- Git có thay đổi Prompt 01 và Prompt 02 chưa commit; không commit/push.
- Composer autoload báo warning class resolution trùng lặp Flysystem/AppServiceProvider, không làm fail lệnh.
- Tinker count check không chạy do PsySH không được phép ghi `C:/Users/admin/AppData/Roaming/PsySH`; đã xác minh bằng MySQL client và Feature Test.
- Chưa triển khai CRUD Department/Employee/Attendance của Prompt 06.
NEXT_STEP: 06_HR
