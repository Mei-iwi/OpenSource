STEP: 07
PROJECT_STATUS: Hoàn thành Attendance management và Employee self-service; chưa triển khai Prompt 08.
LARAVEL_VERSION: 12.65.0
CSS_STACK: Tailwind CSS 4 + Vite, giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade; Authentication, 3 role và protected routes đang hoạt động.
ROLE_STATUS: Admin/HR quản lý HR; Employee chỉ truy cập profile và attendance của chính mình bằng server-side authorization.
DATABASE_STATUS: Runtime development dùng MySQL 8.4.3 tại 127.0.0.1:3306, database hr_management; test dùng hr_management_testing; .env không commit.
SEED_STATUS: Dữ liệu demo Prompt 04 được giữ nguyên.

COMPLETED:
- Đọc state, toàn bộ quy tắc nền và Prompt 07.
- Tạo HR AttendanceController: danh sách filter tháng/năm/phòng ban/nhân viên/search/trạng thái, create/store/edit/update; eager loading và pagination.
- Dùng StoreAttendanceRequest/UpdateAttendanceRequest với status whitelist, exists, unique employee + work_date và check_out >= check_in.
- Giữ unique constraint attendance hiện có trong database.
- Tạo Employee ProfileController và Employee AttendanceController cho profile self-service/lịch sử chấm công theo tháng/năm.
- Employee chỉ cập nhật phone, address, date_of_birth; protected fields không nằm trong validated payload.
- Tạo EmployeePolicy view/update ownership và dùng Gate::authorize server-side.
- Hoàn thiện Blade UI HR attendance, employee profile/history, validation, flash, badge, empty state và pagination.
- Thêm routes và Feature Tests cho attendance, ownership, role authorization và safe profile update.

FILES_CHANGED:
- app/Http/Controllers/HR/AttendanceController.php
- app/Http/Controllers/Employee/ProfileController.php
- app/Http/Controllers/Employee/AttendanceController.php
- app/Http/Requests/StoreAttendanceRequest.php
- app/Http/Requests/UpdateAttendanceRequest.php
- app/Http/Requests/EmployeeProfileUpdateRequest.php
- app/Policies/EmployeePolicy.php
- routes/web.php
- resources/views/hr/attendances/*
- resources/views/employee/profile/*
- resources/views/employee/attendances/index.blade.php
- tests/Feature/AttendanceSelfServiceTest.php
- docs/codex-state.md

COMMANDS_RUN:
- php artisan make:controller / make:request / make:policy
- php artisan route:list
- php artisan view:cache
- composer validate
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test

TEST_RESULTS: route:list đạt, hiển thị 60 routes; view:cache đạt; composer.json hợp lệ; toàn bộ Feature Tests trên MySQL đạt 51 tests, 147 assertions.
OPEN_ISSUES:
- Git còn các thay đổi chưa commit từ Prompt 01-07; không commit/push theo quy tắc.
- Composer autoload còn warning class resolution trùng lặp từ trạng thái trước, không làm fail kiểm tra.
- Báo cáo/thống kê/CSV/Print của Prompt 08 chưa triển khai theo phạm vi yêu cầu.
NEXT_STEP: 08_REPORTS
