# Website Quản lý Nhân sự

Ứng dụng quản lý nhân sự xây dựng bằng PHP 8.3, Laravel 12, Blade, Tailwind CSS 3, Vite và MySQL 8.4. Hệ thống có ba vai trò `admin`, `hr`, `employee`, hỗ trợ tài khoản, phòng ban, hồ sơ nhân viên, chấm công, đơn nghỉ, báo cáo, thư nội bộ và quảng cáo.

Project có thể chạy theo một trong hai cách:

1. Chạy trực tiếp trên máy bằng Laragon/PHP/MySQL/Node.js.
2. Chạy toàn bộ ứng dụng và MySQL bằng Docker Compose.

Hai cách dùng database độc lập. Local mặc định dùng MySQL tại cổng `3306`; Docker dùng MySQL trong container và mở cổng host `3307` để kiểm tra khi cần.

## 1. Tải source

```bash
git clone https://github.com/Mei-iwi/OpenSource.git
cd OpenSource
git switch main
```

Không commit `.env`, mật khẩu thật, `vendor/`, `node_modules/`, file upload hoặc dữ liệu database.

## 2. Cách 1: chạy trực tiếp trên máy

### 2.1. Yêu cầu

- PHP 8.3 với các extension `pdo_mysql`, `mbstring`, `openssl`, `fileinfo` và `zip`.
- Composer 2.
- Node.js 22 và npm.
- MySQL 8.4 hoặc bản tương thích.
- Git. Trên Windows có thể dùng Laragon để cung cấp PHP, Apache và MySQL.

Kiểm tra công cụ:

```bash
php --version
composer --version
node --version
npm --version
```

### 2.2. Cài dependency

```bash
composer install
npm ci
```

### 2.3. Tạo database local

Khởi động MySQL trong Laragon hoặc MySQL service, sau đó tạo database:

```sql
CREATE DATABASE hr_management
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

### 2.4. Tạo file môi trường

PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Command Prompt hoặc Git Bash:

```bash
cp .env.example .env
php artisan key:generate
```

Kiểm tra phần database trong `.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hr_management
DB_USERNAME=root
DB_PASSWORD=
```

Nếu MySQL local có mật khẩu, điền đúng `DB_PASSWORD`. Sau khi sửa `.env`, chạy:

```bash
php artisan optimize:clear
```

### 2.5. Migration và seed dữ liệu local

Lần chạy đầu tiên, tạo bảng và dữ liệu demo bằng một lệnh:

```bash
php artisan migrate --seed
```

Nếu đã migrate nhưng chưa có dữ liệu demo, hoặc muốn cập nhật lại dữ liệu demo:

```bash
php artisan db:seed
```

Seeder dùng `updateOrCreate`, vì vậy có thể chạy lại mà không tạo trùng các mã định danh chính.

Chỉ khi muốn xóa toàn bộ database local và tạo lại từ đầu:

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh` xóa toàn bộ bảng và dữ liệu trong database đang kết nối. Trước khi chạy phải kiểm tra `DB_DATABASE=hr_management` và chắc chắn đây là database local có thể xóa.

### 2.6. Khởi động local

Mở hai terminal tại thư mục project.

Terminal 1 — Laravel:

```bash
php artisan serve
```

Terminal 2 — Vite:

```bash
npm run dev
```

Truy cập: <http://localhost:8000>

Nếu không cần Vite development server, có thể build frontend rồi chỉ chạy Laravel:

```bash
npm run build
php artisan serve
```

## 3. Cách 2: chạy bằng Docker

### 3.1. Yêu cầu

- Docker Desktop hoặc Docker Engine có Docker Compose v2.
- Git.
- Cổng `8080` cho website và `3307` cho MySQL Docker đang trống.

Không cần cài PHP, Composer, Node.js hoặc MySQL trực tiếp trên máy khi dùng cách này.

### 3.2. Build và khởi động container

```bash
docker compose up -d --build
```

Kiểm tra trạng thái và log:

```bash
docker compose ps
docker compose logs -f app
```

Khi MySQL sẵn sàng, container `app` tự thực hiện:

1. Tạo APP_KEY local và lưu trong volume nếu chưa cung cấp APP_KEY.
2. Xóa cache cấu hình và Blade cũ.
3. Chạy `php artisan migrate --force`.
4. Khởi động Laravel tại cổng `8000` trong container.

Truy cập website: <http://localhost:8080>

MySQL Docker được ánh xạ ra máy host tại `127.0.0.1:3307`:

| Thông số | Giá trị |
|---|---|
| Host từ container app | `db` |
| Host từ máy thật | `127.0.0.1` |
| Port từ máy thật | `3307` |
| Database | `hr_management` |
| User ứng dụng | `hr_user` |
| Password ứng dụng | `hr_password` |
| Root password | `local_root_password` |

Các mật khẩu này chỉ dành cho Docker local và được khai báo trong `docker-compose.yml`.

### 3.3. Seed dữ liệu trong Docker

Migration được chạy tự động khi container khởi động, nhưng seeder không tự chạy. Sau lần khởi động đầu tiên, chạy:

```bash
docker compose exec app php artisan db:seed --force
```

Có thể chạy lại cùng lệnh để cập nhật dữ liệu demo.

Nếu muốn xóa toàn bộ database Docker và tạo lại bảng cùng dữ liệu demo:

```bash
docker compose exec app php artisan migrate:fresh --seed --force
```

Lệnh trên chỉ nên dùng cho Docker local vì nó xóa toàn bộ dữ liệu trong database Docker.

### 3.4. Các lệnh Docker thường dùng

```bash
# Xem log
docker compose logs -f app
docker compose logs -f db

# Chạy Artisan
docker compose exec app php artisan route:list
docker compose exec app php artisan migrate:status
docker compose exec app php artisan optimize:clear

# Dừng nhưng giữ database và upload
docker compose down

# Khởi động lại với dữ liệu cũ
docker compose up -d

# Build lại sau khi source/dependency thay đổi
docker compose up -d --build
```

Xóa container và toàn bộ volume database/upload Docker:

```bash
docker compose down -v
```

`docker compose down -v` xóa vĩnh viễn database Docker, APP_KEY đã lưu và các file upload trong Docker.

## 4. Dữ liệu demo

Seeder tạo 20 tài khoản gồm 1 Admin, 2 HR và 17 Employee. Mật khẩu chung:

```text
Password123!
```

| Vai trò | Email |
|---|---|
| Admin | `quan.nm@admin.hr-management.com` |
| HR | `anh.tn@hr.hr-management.com` |
| HR | `ha.ltt@hr.hr-management.com` |
| Employee | `huy.pq@emp.hr-management.com` |
| Employee | `nam.nh@emp.hr-management.com` |

Các tài khoản demo chỉ dùng cho học tập và phát triển local.

## 5. Chuyển đổi giữa local và Docker

Hai môi trường không dùng chung database:

- Local: MySQL `127.0.0.1:3306`, cấu hình trong `.env`.
- Docker: MySQL service `db:3306`, ánh xạ ra host `127.0.0.1:3307`.

Chuyển sang local:

```bash
docker compose down
php artisan optimize:clear
php artisan serve
```

Chuyển sang Docker:

```bash
docker compose up -d --build
```

Không đổi `.env` local sang hostname `db`; biến database của Docker đã được khai báo riêng trong `docker-compose.yml`.

## 6. Xử lý lỗi thường gặp

### Local không kết nối được MySQL

- Kiểm tra MySQL/Laragon đang chạy.
- Kiểm tra database `hr_management` đã tồn tại.
- Kiểm tra các biến `DB_*` trong `.env`.
- Chạy `php artisan optimize:clear` sau khi sửa `.env`.

### Docker app chưa healthy

```bash
docker compose ps
docker compose logs app
docker compose logs db
```

MySQL lần đầu có thể cần vài chục giây để khởi tạo. App chỉ chạy migration sau khi healthcheck MySQL thành công.

### Docker báo cổng đã được sử dụng

Đóng chương trình đang dùng cổng `8080` hoặc `3307`, hoặc đổi phần bên trái của mapping trong `docker-compose.yml`, ví dụ `8081:8000`.

### Lỗi 419 Page Expired

Dùng đúng URL: local là `http://localhost:8000`, Docker là `http://localhost:8080`. Sau đó chạy:

```bash
# Local
php artisan optimize:clear

# Docker
docker compose exec app php artisan optimize:clear
```

### Source thay đổi nhưng Docker chưa cập nhật

Docker image chứa source và frontend tại thời điểm build. Tạo lại image:

```bash
docker compose up -d --build
```

## 7. Email xác thực và đặt lại mật khẩu

Hệ thống không mở đăng ký công khai. Admin hoặc HR tạo hồ sơ và tài khoản trong cùng một biểu mẫu tại **Nhân viên → Tạo nhân viên và tài khoản**. Admin có thể chọn vai trò HR hoặc Nhân viên; HR chỉ có thể tạo Nhân viên. Mã được cấp tự động theo vai trò lúc tạo (`HR-xxxx` hoặc `EMP-xxxx`) và giữ nguyên nếu vai trò thay đổi sau này. Mật khẩu ban đầu là ngày sinh theo định dạng `ddmmyyyy`, ví dụ `15/08/2000` thành `15082000`; thông tin đăng nhập được hiển thị tại trang chi tiết ngay sau khi tạo.

Nhân viên mở **Hồ sơ cá nhân** từ menu tài khoản để cập nhật ảnh, số điện thoại và địa chỉ. Họ tên, email, ngày sinh và thông tin công việc do người có quyền quản lý nhân sự cập nhật. Khi Admin đổi vai trò, hệ thống lưu vai trò cũ, vai trò mới, người thực hiện, thời điểm và ghi chú như `emp -> hr` trong lịch sử tài khoản.

- **Tự đổi mật khẩu:** Hồ sơ cá nhân → nhập mật khẩu hiện tại → Gửi liên kết xác thực → mở email → đặt mật khẩu mới.
- **Quên mật khẩu:** chọn Quên mật khẩu tại trang đăng nhập.
- **Admin hỗ trợ:** Tài khoản → Chi tiết → Gửi email đặt lại mật khẩu. Admin không nhận mật khẩu mới của người dùng.
- Liên kết hết hạn sau 60 phút, chỉ dùng một lần. Gửi lại sau ít nhất 60 giây. Sau khi đổi mật khẩu, các phiên đăng nhập cũ bị vô hiệu hóa.

Cấu hình Gmail trong `.env` ở thư mục gốc (cả local và Docker đọc các biến này):

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-account@gmail.com
MAIL_PASSWORD=your-google-app-password
MAIL_FROM_ADDRESS=your-account@gmail.com
MAIL_FROM_NAME="Công ty TNHH 4 thành viên CTQ"
```

Dùng mật khẩu ứng dụng của Gmail gửi thư, không dùng mật khẩu đăng nhập Google. Xem [hướng dẫn mật khẩu ứng dụng của Google](https://support.google.com/accounts/answer/185833?hl=vi). Không đưa `.env` hoặc mật khẩu vào Git. Các email demo của seeder không phải hộp thư thật; muốn nhận thư, admin cần cập nhật email tài khoản thành địa chỉ có thể truy cập.

Sau khi sửa cấu hình:

```bash
# Local: APP_URL trong .env phải trỏ tới URL local bạn đang dùng
php artisan config:clear

# Docker: tạo lại container để nhận MAIL_* mới; không xóa volume
docker compose up -d --build --force-recreate app
```

Docker mặc định dùng `http://localhost:8080` cho liên kết. Nếu đổi cổng/host, cập nhật `APP_URL` của service app tương ứng. Khi chưa cần gửi thư thật, dùng `MAIL_MAILER=log`; thư và liên kết chỉ ghi vào `storage/logs/laravel.log`. Không cần chạy queue worker vì luồng gửi thư chạy đồng bộ.

## 8. Cấu trúc chính

- `app/Http/Controllers`: controller theo module Admin, HR và Employee.
- `app/Http/Requests`: validation và authorization cho form phức tạp.
- `app/Models`: Eloquent model và relationships.
- `database/migrations`: cấu trúc database.
- `database/seeders/DatabaseSeeder.php`: dữ liệu demo.
- `resources/views`: giao diện Blade.
- `resources/js`, `resources/css`: JavaScript và Tailwind CSS.
- `routes/web.php`, `routes/auth.php`: route web và authentication.
- `Dockerfile`, `docker-compose.yml`, `docker/start.sh`: môi trường Docker local.

## 9. Đối chiếu đề cương và chuẩn đầu ra

Căn cứ: **Lập trình mã nguồn mở, đề cương v1.2**, mục 5 (trang 4), mục 8 (trang 10), Rubric 2 (trang 13–14). Điểm môn gồm bài tập 10%, kiểm tra thực hành 40%, đồ án 50%. Trong điểm đồ án, chức năng chiếm 40%, giao diện 30%, hình thức báo cáo 10%, nội dung báo cáo 10%, tương tác nhóm 10%. Source hỗ trợ minh chứng sản phẩm, không thay thế báo cáo, phần vấn đáp hoặc đóng góp thực tế của từng thành viên.

| Chuẩn đầu ra | Minh chứng trong dự án | Nội dung cần trình bày khi bảo vệ |
|---|---|---|
| CLO1: PHP, OOP, CSDL, Laravel | Controller theo module, FormRequest, Eloquent relationships, migration, seeder, middleware; `EmployeeCodeGenerator` được inject qua container | Luồng route → middleware → request → controller → model → Blade; prepared bindings của Eloquent/PDO; transaction khi tạo tài khoản và hồ sơ |
| CLO2: đánh giá website | Sáng/tối, menu bốn vị trí, tìm kiếm/phân trang, trạng thái rỗng, thông báo lỗi form, báo cáo in/CSV | Đánh giá khả năng đọc, nhất quán, thao tác bàn phím, màn hình nhỏ; ghi nhận hạn chế bằng ảnh và kết quả thực tế |
| CLO3: đánh giá công việc nhóm | Lịch sử commit và pull request để đối chiếu phần việc | Nhóm tự ghi người thực hiện, phần việc, kết quả, người review; không dùng tài khoản demo nhân sự làm minh chứng thành viên nhóm |
| CLO4: triển khai website cho tổ chức | Quản lý nhân sự ba vai trò; MySQL, upload riêng tư, email reset; hướng dẫn local và Docker ở trên | Khởi động từ source mới, migrate/seed, demo quy trình nghiệp vụ; Docker hiện là môi trường local, chưa phải cấu hình production |
| CLO5: tự học và trách nhiệm | Dependency có lockfile, cấu hình mẫu không chứa bí mật, hướng dẫn tái lập | Giải thích lựa chọn công nghệ, nguồn tham khảo, phần tự học và cải tiến thực tế của mỗi thành viên |

Phạm vi nghiệp vụ: Admin quản lý tài khoản và vai trò; Admin/HR quản lý phòng ban, hồ sơ, chấm công, duyệt đơn và báo cáo; Employee dùng hồ sơ và dữ liệu cá nhân. Tạo tài khoản gắn hồ sơ trong một transaction; mã nhân viên được cấp theo vai trò ban đầu và không đổi khi chuyển vai trò. HR không được sửa hồ sơ Admin. Duyệt/hủy đơn chỉ thành công khi đơn vẫn đang chờ, tránh ghi đè thao tác của người khác.

Bộ lọc chấm công và báo cáo dùng chung `AttendanceFilterRequest` và scope `Attendance::filtered()`. Danh sách phân trang; bản in lấy toàn bộ kết quả lọc; CSV đọc theo từng lô 500 bản ghi và vô hiệu hóa tiền tố công thức trong dữ liệu nhập bởi người dùng. Không thêm tầng repository hoặc thay Laravel bằng PHP thuần vì không cần thiết cho phạm vi đồ án.

Các mục học tập như PDO thuần, abstract class/interface, Factory vẫn cần được hiểu và thực hành theo đề giảng viên; không khẳng định source này bao phủ toàn bộ bài tập của môn. Eloquent dùng PDO bên dưới, nhưng không thay thế phần giải thích prepared statements khi vấn đáp. Dự án hiện dùng seeder xác định để demo có thể lặp lại.

### Trình tự trình diễn đề xuất

1. Chạy ứng dụng từ môi trường sạch theo mục 2 hoặc 3; seed dữ liệu demo.
2. Admin tạo phòng ban và nhân viên: không nhập mã/mật khẩu, nhận mã theo vai trò và mật khẩu ngày sinh; kiểm tra cả danh sách nhân viên lẫn tài khoản.
3. Đổi vai trò rồi kiểm tra mã giữ nguyên và lịch sử thay đổi có người thực hiện.
4. Đăng nhập Employee; xác nhận không truy cập được trang Admin/HR hoặc hồ sơ nghỉ phép của người khác; cập nhật thông tin cá nhân được phép.
5. Chấm công có ảnh, gửi đơn nghỉ; HR duyệt; Employee không thể hủy đơn đã duyệt. Nếu demo camera, cấp quyền trình duyệt và dùng localhost hoặc HTTPS.
6. Lọc báo cáo theo tháng/phòng ban; đối chiếu tổng số với CSV và bản in nhiều hơn 20 dòng. Thử dữ liệu không hợp lệ và bộ lọc không có kết quả.
7. Đổi sáng/tối, thử menu và màn hình nhỏ; đăng xuất qua trang tạm biệt 5 giây; thử truy cập lại trang cần đăng nhập.
8. Demo reset mật khẩu bằng hộp thư thật do nhóm kiểm soát, hoặc dùng `MAIL_MAILER=log` và nói rõ đây là chế độ mô phỏng gửi thư.

Để đạt nhóm điểm cao nhất, nhóm cần nộp báo cáo theo mẫu giảng viên: yêu cầu, phân quyền, thiết kế CSDL, giải thích các luồng chính, ảnh kết quả, đánh giá ưu/nhược điểm, phân công thực tế và tài liệu tham khảo. Đề cương không kèm đặc tả riêng cho đề tài quản lý nhân sự, nên cần đối chiếu thêm yêu cầu đề tài đã được giảng viên duyệt.

## 10. Sao lưu và phục hồi MySQL local

Ví dụ dưới đây chạy trong terminal có `mysql` và `mysqldump` trên PATH. Dùng `--result-file` để tránh lỗi mã hóa khi redirect bằng PowerShell. Mật khẩu nhập tại lời nhắc, không đưa vào command hoặc commit.

```bash
mysqldump -h 127.0.0.1 -P 3306 -u root -p --single-transaction --no-tablespaces --result-file=hr-management-backup.sql hr_management
```

Phục hồi thử vào database **riêng**, không ghi đè dữ liệu đang dùng:

```bash
mysql -h 127.0.0.1 -P 3306 -u root -p -e "CREATE DATABASE hr_management_restore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h 127.0.0.1 -P 3306 -u root -p hr_management_restore -e "source hr-management-backup.sql"
```

Với MySQL Docker, dùng client trên máy host và đổi `-P 3306 -u root` thành `-P 3307 -u hr_user` khi sao lưu; việc tạo database phục hồi cần tài khoản root Docker. Sao lưu cả thư mục upload riêng tư (hoặc volume `app_storage` với Docker) và giữ APP_KEY an toàn nếu cần phục hồi toàn bộ ứng dụng. File SQL và upload có thể chứa dữ liệu cá nhân; lưu ngoài Git.
