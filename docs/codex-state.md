STEP: 06
PROJECT_STATUS: Hoàn thành CRUD phòng ban và nhân viên; chưa triển khai Attendance/Prompt 07.
LARAVEL_VERSION: 12.65.0
CSS_STACK: Tailwind CSS 4 + Vite, giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade; Authentication, 3 role và protected routes đang hoạt động.
ROLE_STATUS: admin/hr được phép quản trị HR; employee bị chặn server-side bằng middleware role:admin,hr.
DATABASE_STATUS: Runtime development dùng MySQL 8.4.3 tại 127.0.0.1:3306, database hr_management; test dùng hr_management_testing; .env không commit.
SEED_STATUS: Dữ liệu demo Prompt 04 được giữ nguyên.

COMPLETED:
- Đọc codex-state.md, toàn bộ quy tắc nền và Prompt 06.
- Tạo DepartmentController và Form Requests cho CRUD phòng ban; tìm kiếm, eager/count, pagination và giữ query string.
- Chặn xóa phòng ban khi đang có nhân viên.
- Tạo EmployeeController và Form Requests cho tạo, xem, sửa nhân viên; không có hard delete.
- Tạo User role employee và Employee đồng thời trong DB::transaction khi thêm nhân viên.
- Cập nhật User và Employee trong transaction khi sửa; validation unique mã nhân viên/email.
- Hoàn thiện Blade UI: search/filter/reset, pagination, validation errors, flash messages, badges, CSRF và PUT/PATCH.
- Thêm Feature Tests cho authorization, CRUD, transaction, validation, search/filter và pagination.

FILES_CHANGED:
- app/Http/Controllers/HR/DepartmentController.php
- app/Http/Controllers/HR/EmployeeController.php
- app/Http/Requests/StoreDepartmentRequest.php
- app/Http/Requests/UpdateDepartmentRequest.php
- app/Http/Requests/StoreEmployeeRequest.php
- app/Http/Requests/UpdateEmployeeRequest.php
- routes/web.php
- resources/views/hr/departments/*.blade.php
- resources/views/hr/employees/*.blade.php
- tests/Feature/HrCrudTest.php
- docs/codex-state.md

COMMANDS_RUN:
- php artisan route:list
- php artisan view:cache
- composer validate
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test

TEST_RESULTS: route:list đạt, hiển thị 51 routes; view:cache đạt; composer.json hợp lệ; toàn bộ Feature Tests trên MySQL đạt 46 tests, 125 assertions.
OPEN_ISSUES:
- Git còn các thay đổi chưa commit từ Prompt 01-06; không commit/push theo quy tắc.
- Composer autoload còn warning class resolution trùng lặp Flysystem/AppServiceProvider từ trạng thái trước, không làm fail kiểm tra.
- Attendance và Prompt 07 chưa triển khai theo phạm vi yêu cầu.
NEXT_STEP: 07_ATTENDANCE_SELF
