# Website Quản lý Nhân sự

Ứng dụng Laravel 12 render bằng Blade cho quản lý tài khoản, phòng ban, nhân viên, chấm công và báo cáo.

## 1. Thành viên mới: clone code

Yêu cầu: Git, PHP 8.3, Composer, Node.js 22+, npm và MySQL 8.4. Trên Windows có thể dùng Laragon để chạy Apache/MySQL.

```bash
git clone https://github.com/Mei-iwi/OpenSource.git
cd OpenSource
git switch main
composer install
npm ci
```

Nếu làm trên branch chức năng:

```bash
git fetch origin
git switch -c feat/ten-chuc-nang
```

Không sửa trực tiếp `main`. Trước khi code chạy `git status`, sau khi code chạy test và tạo Pull Request; không commit `.env`, token, mật khẩu thật hoặc thư mục `vendor/`, `node_modules/`.

## 2. Chạy bằng Laragon/MySQL (khuyến nghị cho development)

### Tạo database

Mở MySQL trong Laragon, tạo database tên `hr_management` bằng HeidiSQL hoặc lệnh:

```sql
CREATE DATABASE hr_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Copy file môi trường:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Kiểm tra `.env` có các giá trị sau. Nếu Laragon đặt mật khẩu cho `root`, điền mật khẩu local vào `DB_PASSWORD` nhưng không commit file này:

```dotenv
APP_URL=http://localhost:8000
APP_LOCALE=vi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_management
DB_USERNAME=root
DB_PASSWORD=
```

Chạy migration, dữ liệu demo và symbolic link ảnh:

```bash
php artisan migrate --seed
php artisan storage:link
```

Khởi động:

```bash
php artisan serve
npm run dev
```

Mở `http://localhost:8000`. Nếu đã mở bằng `127.0.0.1`, hãy dùng thống nhất hostname để tránh lỗi session/CSRF 419.

## 3. Tài khoản demo

Seeder tạo các tài khoản sau, mật khẩu chung là `Password123!`:

| Vai trò | Email |
|---|---|
| Quản trị viên | `admin@example.com` |
| Nhân sự | `hr@example.com`, `hr2@example.com` |
| Nhân viên | `employee1@example.com` ... `employee12@example.com` |

Chỉ dùng tài khoản này trong development/demo, không dùng production.

## 4. Chạy bằng Docker

Docker Compose cung cấp PHP 8.3 + Apache và MySQL 8.4 riêng cho project. Không cần cài PHP/Composer/MySQL trên máy, chỉ cần Docker Desktop.

```bash
docker compose build
docker compose up -d db
docker compose run --rm app php artisan key:generate --show
```

Copy key được in ra vào biến `APP_KEY` trong `docker-compose.yml` (chỉ dùng key local). Sau đó:

```bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Mở `http://localhost:8080`. MySQL trong container có database `hr_management`, host nội bộ là `db`, port host `3307`. Dữ liệu được lưu trong volume `hr_mysql_data`.

Xem log hoặc dừng hệ thống:

```bash
docker compose logs -f app
docker compose down
```

`docker-compose.yml` dùng mật khẩu rỗng chỉ cho môi trường local demo. Production phải dùng secret manager và tài khoản database có quyền tối thiểu.

## 5. Test và build frontend

Test bắt buộc dùng MySQL test database riêng `hr_management_testing`, không dùng SQLite. Đảm bảo MySQL đang chạy và database test đã tồn tại:

```sql
CREATE DATABASE hr_management_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Chạy kiểm tra:

```bash
composer validate
php artisan route:list
php artisan view:cache
php artisan test
npm run build
```

`phpunit.xml` đã cấu hình `DB_CONNECTION=mysql` và `DB_DATABASE=hr_management_testing`. Không chạy `migrate:fresh --seed` trên `hr_management`; lệnh đó chỉ dành cho database development/demo đã xác nhận an toàn.

## 6. Vai trò và chức năng

- `admin`: quản lý tài khoản, vai trò, khóa/mở tài khoản và toàn bộ module HR.
- `hr`: quản lý phòng ban, nhân viên, chấm công và báo cáo.
- `employee`: xem hồ sơ, cập nhật trường cá nhân được phép và xem chấm công của mình.

Các module hiện có: Authentication, User Management, Department/Employee CRUD, Attendance, Employee Self-Service, Reports/CSV/Print, AJAX kiểm tra mã nhân viên và Avatar Upload.

## 7. Quy trình đóng góp

1. Cập nhật branch từ `main` trước khi bắt đầu.
2. Tạo branch tên ngắn theo chức năng.
3. Giữ thay đổi nhỏ, không đổi kiến trúc hoặc version package nếu chưa thống nhất.
4. Chạy test/build trước khi commit.
5. Commit message rõ ràng, ví dụ `feat: add attendance filter` hoặc `docs: update setup guide`.
6. Push branch và mở Pull Request, ghi rõ files changed, tests và ảnh minh chứng nếu có.

## 8. Cấu trúc và tài liệu

Source chính nằm trong `app/Http/Controllers`, `app/Http/Requests`, `app/Models`, `database/migrations`, `database/factories`, `database/seeders`, `resources/views`, `routes` và `tests/Feature`.

Tài liệu đồ án nằm trong `docs/`: kiến trúc, ERD, yêu cầu, test, evidence, deployment và checklist trình diễn. Không tự tạo screenshot hoặc thông tin teamwork chưa được xác minh.

## 9. Lưu ý an toàn

Không commit `.env`, `APP_KEY` thật, mật khẩu, token, dữ liệu production hoặc file upload cá nhân. Không dùng `db:wipe`. Khi đổi database, kiểm tra chính xác `DB_DATABASE` trước khi chạy migration hoặc seed.
