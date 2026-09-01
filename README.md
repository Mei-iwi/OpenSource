# Website Quản lý Nhân sự

## 1. Giới thiệu ngắn

Website Quản lý Nhân sự là ứng dụng web xây dựng bằng Laravel 12, Blade và MySQL. Project hỗ trợ quản lý tài khoản, phòng ban, nhân viên, chấm công, đơn nghỉ và báo cáo theo ba vai trò `admin`, `hr` và `employee`.

Hướng dẫn dưới đây dành cho thành viên mới clone project về máy và chạy môi trường development/demo.

## 2. Clone và cấu hình project

### 2.1. Yêu cầu môi trường

- Git.
- PHP 8.3.x.
- Composer 2.x.
- Node.js 22 trở lên và npm.
- MySQL 8.4 hoặc phiên bản tương thích.
- Trên Windows có thể dùng Laragon để chạy PHP, Apache và MySQL.

### 2.2. Clone source và cài package

```bash
git clone https://github.com/Mei-iwi/OpenSource.git
cd OpenSource
git switch main
composer install
npm ci
```

Kiểm tra source sau khi clone:

```bash
git status
php --version
composer --version
```

Nếu làm chức năng mới, tạo branch riêng và không sửa trực tiếp `main`:

```bash
git fetch origin
git switch -c feat/ten-chuc-nang
```

`vendor/` và `node_modules/` không được commit. Hai thư mục này được tạo lại bằng `composer install` và `npm ci`.

### 2.3. Tạo và cấu hình MySQL

Khởi động MySQL trong Laragon, HeidiSQL hoặc MySQL service. Tạo database development:

```sql
CREATE DATABASE hr_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Tạo file môi trường từ file mẫu:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Mở `.env` và kiểm tra cấu hình MySQL:

```dotenv
APP_NAME="Website Quan ly Nhan su"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_LOCALE=vi

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_management
DB_USERNAME=root
DB_PASSWORD=
```

Nếu MySQL/Laragon có mật khẩu cho user `root`, điền mật khẩu local vào `DB_PASSWORD`. Không commit `.env` hoặc mật khẩu thật.

Kiểm tra Laravel kết nối được database trước khi migrate:

```bash
php artisan config:clear
php artisan about
```

### 2.4. Sinh database và dữ liệu demo

Chạy migration và seeder:

```bash
php artisan migrate --seed
php artisan storage:link
```

Sau bước này, Laravel sẽ tạo các bảng và dữ liệu demo gồm tài khoản, phòng ban, nhân viên và lịch sử chấm công. Dữ liệu được sinh từ migration, factory và `DatabaseSeeder`; không nằm sẵn trong Git.

Nếu database development cần tạo lại hoàn toàn, chỉ dùng lệnh dưới đây sau khi đã xác nhận đúng database `hr_management` là database local/demo:

```bash
php artisan migrate:fresh --seed
```

Không chạy lệnh này trên production hoặc database có dữ liệu thật.

### 2.5. Tài khoản mặc định sau khi seed

Mật khẩu chung cho các tài khoản demo là `Password123!`.

| Vai trò | Email đăng nhập | Ghi chú |
|---|---|---|
| Admin | `admin@example.com` | Toàn quyền hệ thống |
| HR | `hr@example.com` | Quản lý nghiệp vụ nhân sự |
| HR | `hr2@example.com` | Tài khoản HR thứ hai |
| Employee | `employee1@example.com` đến `employee12@example.com` | Chỉ xem và cập nhật dữ liệu cá nhân được phép |

Các tài khoản trên chỉ dùng cho development/demo. Khi triển khai thật cần đổi mật khẩu và không sử dụng thông tin mặc định.

### 2.6. Khởi động ứng dụng

Mở hai terminal tại thư mục project.

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Truy cập `http://localhost:8000`. Nếu dùng `127.0.0.1`, hãy dùng thống nhất hostname trong toàn bộ phiên làm việc để tránh lỗi session/CSRF `419 Page Expired`.

## 3. Chạy bằng Docker

Docker Compose cung cấp PHP 8.3 + Apache và MySQL 8.4 riêng cho project. Chỉ cần cài Docker Desktop:

```bash
docker compose build
docker compose up -d db
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Truy cập `http://localhost:8080`. Container MySQL dùng database `hr_management`, hostname nội bộ `db`, port host `3307` và volume `hr_mysql_data` để lưu dữ liệu.

```bash
docker compose logs -f app
docker compose down
```

Cấu hình mật khẩu rỗng trong Compose chỉ phục vụ local demo. Production phải dùng secret an toàn và tài khoản database có quyền tối thiểu.

## 4. Giới thiệu chi tiết dự án

### 4.1. Mục đích

Project mô phỏng hệ thống quản lý nhân sự cho doanh nghiệp nhỏ. Mục tiêu là áp dụng Laravel MVC, Eloquent ORM, migration, seeder, factory, Form Request, middleware, policy, Blade, MySQL và kiểm thử Feature trong một ứng dụng thực tế ở mức đồ án sinh viên.

### 4.2. Vai trò và quyền hạn

- `admin`: quản lý tài khoản, role, trạng thái khóa/mở tài khoản và truy cập toàn bộ chức năng HR.
- `hr`: quản lý phòng ban, nhân viên, chấm công và báo cáo; không được thay đổi role admin.
- `employee`: xem hồ sơ của mình, cập nhật các trường cá nhân được phép và xem lịch sử chấm công của mình.

Quyền được kiểm tra ở server-side bằng authentication, middleware và policy/ownership; không chỉ ẩn chức năng trên giao diện.

### 4.3. Chức năng chính

- Đăng nhập, đăng xuất và quản lý session.
- Admin quản lý tài khoản, role, trạng thái hoạt động, tìm kiếm, lọc và phân trang.
- Admin/HR CRUD phòng ban và nhân viên.
- Tạo nhân viên đồng thời tạo user role `employee` trong transaction.
- Quản lý chấm công với các trạng thái `present`, `late`, `absent`, `leave`.
- Employee tự quản lý thông tin cá nhân được phép và xem chấm công của mình.
- Dashboard và báo cáo thống kê theo phòng ban, trạng thái, nhân viên và thời gian.
- Xuất CSV theo tập dữ liệu đã lọc và giao diện Print/Save as PDF bằng trình duyệt.
- Kiểm tra mã nhân viên bằng Fetch/AJAX và JSON.
- Upload ảnh đại diện vào storage public.
- Employee gửi đơn nghỉ; Admin/HR xem xét, duyệt hoặc từ chối đơn đang chờ.
- Giao diện Blade responsive, Tailwind CSS 4 và Vite.

### 4.4. Công nghệ và cấu trúc

- Backend: PHP 8.3, Laravel 12, Laravel MVC.
- Database: MySQL; Eloquent ORM và quan hệ `User`, `Employee`, `Department`, `Attendance`, `LeaveRequest`.
- Frontend: Blade, Tailwind CSS 4, Vite và JavaScript cơ bản.
- Kiểm thử: PHPUnit/Pest Feature Tests với database MySQL `hr_management_testing`.
- CI: GitHub Actions dùng PHP 8.3 và MySQL test database riêng.

Source chính nằm trong `app/Http/Controllers`, `app/Http/Requests`, `app/Models`, `database/migrations`, `database/factories`, `database/seeders`, `resources/views`, `routes` và `tests/Feature`.

Tài liệu đồ án nằm trong `docs/`, gồm kiến trúc, ERD, yêu cầu, test, evidence, deployment và các checklist trình diễn.

## 5. Kiểm thử và build frontend

Test bắt buộc dùng MySQL test database riêng, không fallback SQLite:

```sql
CREATE DATABASE hr_management_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

Chạy các kiểm tra:

```bash
composer validate
php artisan route:list
php artisan view:cache
php artisan test
npm run build
```

`phpunit.xml` cấu hình test dùng `DB_CONNECTION=mysql` và database `hr_management_testing`. Không chạy `migrate:fresh --seed` trên database development khi chưa xác nhận đúng môi trường.

## 6. Quy trình đóng góp

1. Cập nhật branch từ `main` trước khi bắt đầu.
2. Tạo branch theo chức năng, ví dụ `feat/attendance-filter`.
3. Chạy `git status` trước khi sửa và chạy test/build trước khi commit.
4. Commit message rõ ràng, ví dụ `feat: add attendance filter` hoặc `docs: update setup guide`.
5. Push branch và mở Pull Request, ghi rõ file thay đổi, lệnh kiểm tra và ảnh minh chứng nếu có.

Không force-push, không bịa remote, không commit `.env`, APP_KEY, token, mật khẩu thật, dữ liệu production, `vendor/` hoặc `node_modules/`.

## 7. Xử lý lỗi thường gặp

### Lỗi không kết nối được MySQL

Kiểm tra MySQL đang chạy, database đã tồn tại và các biến `DB_*` trong `.env` đúng. Sau khi đổi `.env`, chạy:

```bash
php artisan config:clear
php artisan migrate --seed
```

### Lỗi 419 Page Expired

Dùng thống nhất `localhost` hoặc `127.0.0.1`, kiểm tra `APP_URL`, xóa cache config và tải lại trang đăng nhập:

```bash
php artisan optimize:clear
```

### Thiếu thư mục hoặc package

```bash
composer install
npm ci
php artisan storage:link
```

## 8. Lưu ý an toàn

Không commit `.env`, APP_KEY thật, mật khẩu, token, dữ liệu production hoặc file upload cá nhân. Không dùng `db:wipe`. Luôn kiểm tra chính xác `DB_DATABASE` trước khi migrate, seed hoặc reset database.
