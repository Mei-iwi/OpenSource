# Deploy Laravel lên Render bằng Docker

Tài liệu này chuẩn bị source cho Render Web Service. Việc tạo database, bucket, credentials, service và deploy thật phải được thực hiện thủ công; không đưa secret vào Git.

## Kiến trúc

- Render Web Service: Docker, branch `main`, Dockerfile `./Dockerfile`, health check `/up`.
- External MySQL/MySQL-compatible: Laravel giữ `DB_CONNECTION=mysql`.
- Cloudflare R2 S3-compatible: avatar và ảnh minh chứng chấm công dùng disk `s3` trong production.
- Render filesystem là ephemeral, không dùng để lưu upload lâu dài.

## Environment variables trên Render

```dotenv
APP_NAME=Quản lý nhân sự
APP_ENV=production
APP_DEBUG=false
APP_KEY=<kết quả php artisan key:generate --show>
APP_URL=<RENDER_URL>
ASSET_URL=<RENDER_URL>
LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=<từ database provider>
DB_PORT=<từ database provider>
DB_DATABASE=<từ database provider>
DB_USERNAME=<từ database provider>
DB_PASSWORD=<từ database provider>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
SESSION_SECURE_COOKIE=true
RUN_SEED=false

AWS_ACCESS_KEY_ID=<R2 access key>
AWS_SECRET_ACCESS_KEY=<R2 secret>
AWS_DEFAULT_REGION=auto
AWS_BUCKET=<R2 bucket>
AWS_ENDPOINT=https://<ACCOUNT_ID>.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
AVATAR_DISK=s3
ATTENDANCE_PROOF_DISK=s3
```

## Trình tự triển khai

1. Tạo external MySQL và lấy đúng năm giá trị `DB_*` từ provider.
2. Tạo Cloudflare R2 bucket và API credentials chỉ có quyền cần thiết.
3. Tạo Render Web Service, runtime Docker, Dockerfile `./Dockerfile`, health check `/up`.
4. Thêm toàn bộ environment variables ở trên; sinh `APP_KEY` cục bộ bằng `php artisan key:generate --show`.
5. Deploy và xem logs. Container tự chạy `php artisan migrate --force`; không chạy `migrate:fresh`.
6. Mở `/up`, `/login`, đăng nhập thử các role và kiểm tra dashboard, CRUD, attendance, reports, CSV, print.
7. Nếu cần dữ liệu demo, đặt `RUN_SEED=true` cho đúng một lần, xác nhận thành công rồi đổi lại `false` và redeploy. Seeder dùng mật khẩu demo cố định, chỉ dành cho demo.
8. Test avatar, ảnh check-in/check-out và quyền truy cập ảnh sau khi đã cấu hình R2.

## R2 và bảo mật file

Object proof là private và được stream qua route có authentication/authorization. Không tạo public object URL. Avatar cũng được phục vụ qua route ứng dụng để không phụ thuộc `public/storage`.

## Troubleshooting

- `/up` lỗi: kiểm tra logs và kết nối MySQL, không in secret ra log.
- `Missing APP_KEY`: bổ sung APP_KEY trong Render Environment.
- Lỗi migration: kiểm tra DB host/port/database/user/password và quyền schema.
- Vite manifest missing: kiểm tra Docker build đã chạy `npm ci` và `npm run build`.
- Ảnh không lưu: kiểm tra R2 endpoint, bucket, credentials, `AVATAR_DISK`/`ATTENDANCE_PROOF_DISK`.
- Camera chỉ hoạt động trên HTTPS và cần quyền browser; nếu bị từ chối vẫn có thể tải ảnh lên.
