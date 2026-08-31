STEP: 07-CI-STABILIZATION
PROJECT_STATUS: Prompt 01-07 hoàn thành; CI workflow đã chuẩn hóa; chưa triển khai Prompt 08.
LARAVEL_VERSION: 12.65.0
PHP_VERSION: Local 8.3.30; GitHub Actions PHP 8.3.
CSS_STACK: Tailwind CSS 4 + Vite, giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade; Authentication, 3 role và protected routes đang hoạt động.
ROLE_STATUS: Admin/HR quản trị HR; Employee self-service có ownership server-side.
DATABASE_STATUS: Runtime development MySQL 8.4.3 `hr_management`; CI/test MySQL 8.4 `hr_management_testing` tại 127.0.0.1:3306; không dùng SQLite fallback.
SEED_STATUS: Dữ liệu demo Prompt 04 được giữ nguyên.

COMPLETED:
- Đọc state và kiểm tra toàn bộ .github/workflows trước khi sửa.
- Xác định workflow cũ có lịch sử matrix PHP 8.2/8.3/8.4; PHP 8.2 không tương thích Pint v1.30.5 yêu cầu PHP ^8.3.
- Xác định workflow hiện tại thiếu MySQL service và vẫn dùng extensions sqlite/pdo_sqlite.
- Cập nhật `.github/workflows/tests.yml`: chỉ PHP 8.3, extensions pdo_mysql/mbstring, MySQL 8.4 health check và database test riêng.
- CI dùng `composer install --prefer-dist --no-interaction --no-progress`, tạo `.env` từ `.env.example`, generate APP_KEY.
- CI dùng `npm ci` và `npm run build`; không chạy composer update, không đổi composer.lock.
- Giữ nguyên RefreshDatabase/test framework; không thêm migration dư thừa trước test.

FILES_CHANGED:
- .github/workflows/tests.yml
- docs/codex-state.md

COMMANDS_RUN:
- Get-Content docs/codex-state.md
- Get-ChildItem/.github/workflows và Get-Content toàn bộ workflow
- git status --short; git log -5 --oneline -- .github/workflows; git diff
- gh run list --limit 10 (bị chặn mạng, không lấy được remote log)
- composer validate
- php artisan route:list
- php artisan view:cache
- cmd /c npm run build
- set DB_CONNECTION=mysql ... DB_DATABASE=hr_management_testing ... php artisan test

TEST_RESULTS: composer validate đạt; route:list hiển thị 60 routes; view:cache đạt; npm build đạt; toàn bộ Feature Tests MySQL đạt 51 tests, 147 assertions.
CI_STATUS: Workflow dùng PHP 8.3 + MySQL 8.4 service, health check trước test, `hr_management_testing`, 127.0.0.1, không SQLite.
OPEN_ISSUES:
- Không truy cập được GitHub Actions remote logs do mạng môi trường bị chặn; root cause xác định qua workflow/lịch sử local.
- Git còn thay đổi chưa commit từ Prompt 01 đến bước CI; không commit/push theo quy tắc.
- Composer autoload warning cũ không làm fail các kiểm tra đã chạy.
- Prompt 08 chưa triển khai theo phạm vi yêu cầu.
NEXT_STEP: 08_REPORTS
