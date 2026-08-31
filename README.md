# Website Quản lý Nhân sự

Website Laravel MVC render bằng Blade cho đồ án quản lý tài khoản, phòng ban, nhân viên, chấm công và báo cáo.

## Chức năng

- Đăng nhập, đăng xuất, mật khẩu và hồ sơ cá nhân.
- Admin quản lý tài khoản HR/Employee, tìm kiếm, lọc, phân trang, đổi vai trò và khóa/mở tài khoản.
- Admin/HR CRUD phòng ban, nhân viên và chấm công.
- Employee chỉ xem hồ sơ và lịch sử chấm công của mình.
- Dashboard, thống kê, CSV theo bộ lọc, giao diện in, Fetch/JSON kiểm tra mã nhân viên và tải ảnh đại diện.

## Vai trò

- `admin`: toàn quyền, gồm quản lý tài khoản và vai trò.
- `hr`: quản lý phòng ban, nhân viên, chấm công và báo cáo; không quản lý vai trò admin.
- `employee`: chỉ dùng các chức năng cá nhân được cấp quyền.

## Công nghệ và cài đặt

PHP 8.3, Laravel 12.65, Laravel Breeze Blade, MySQL 8.4, Tailwind CSS, Vite, Alpine.js, Composer, npm, Git và GitHub Actions.

```bash
git clone <repository-url>
cd project
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Trên Windows dùng `Copy-Item .env.example .env` thay cho `cp`.

## Database

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_management
DB_USERNAME=root
DB_PASSWORD=
```

Chạy `php artisan migrate --seed` và `php artisan storage:link`. Project dùng `users`, `departments`, `employees`, `attendances`, không có bảng `reports`.

Tài khoản demo: `admin@example.com`, `hr@example.com`, `hr2@example.com`, `employee1@example.com` ... `employee12@example.com`; mật khẩu `Password123!`. Chỉ dùng cho development/demo, không dùng trong production.

## Chạy và kiểm thử

```bash
php artisan serve
npm run dev
php artisan test
```

Tests dùng MySQL `hr_management_testing`, không fallback SQLite. Bộ test đã xác nhận: 61 tests, 222 assertions, 0 failures.

GitHub Actions dùng PHP 8.3, MySQL 8.4, `pdo_mysql`, `mbstring`, `npm ci`, `npm run build` và test database `hr_management_testing`.

## Tài liệu và triển khai

Xem `docs/architecture.md`, `docs/diagrams/erd.md`, `docs/report-outline.md`, `docs/requirements-matrix.md`, `docs/test/`, `docs/evidence/` và `docs/deployment.md`.

Không commit `.env`, mật khẩu thật, token hoặc thông tin production. `php artisan migrate:fresh --seed` xóa dữ liệu và chỉ dùng với database development/demo đã xác nhận.
