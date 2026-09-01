# Deploy Laravel lên Render: Postgres + Persistent Disk

## Kiến trúc

Render Web Service chạy Docker, kết nối Render Postgres và lưu upload trên disk local `persistent_uploads`. Persistent Disk là bắt buộc nếu cần giữ avatar/ảnh chấm công qua restart, redeploy hoặc spin down. Free Web Service không có disk chỉ là `DEMO_EPHEMERAL_STORAGE`; file có thể mất.

## Environment trên Render

```dotenv
APP_NAME=Quản lý nhân sự
APP_ENV=production
APP_DEBUG=false
APP_KEY=<kết quả php artisan key:generate --show>
APP_URL=<RENDER_URL>
ASSET_URL=<RENDER_URL>
LOG_CHANNEL=stderr
LOG_LEVEL=info
DB_CONNECTION=pgsql
DB_HOST=<Render Postgres internal host>
DB_PORT=5432
DB_DATABASE=<Render Postgres database>
DB_USERNAME=<Render Postgres user>
DB_PASSWORD=<Render Postgres password>
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync
MEDIA_DISK=persistent_uploads
MEDIA_DISK=persistent_uploads
AVATAR_DISK=persistent_uploads
ATTENDANCE_PROOF_DISK=persistent_uploads
UPLOAD_STORAGE_PATH=/var/data/uploads
RUN_SEED=false
SESSION_SECURE_COOKIE=true
APP_LOCALE=vi
APP_FALLBACK_LOCALE=en
```

Không dùng `AWS_*`, R2, S3 hoặc SQLite production. Không commit secrets.

## Trình tự tạo hạ tầng

1. Merge source vào `main`.
2. Render Dashboard → New → PostgreSQL; ưu tiên cùng region với Web Service.
3. Lấy Internal DB credentials, không ghi vào source.
4. Tạo Web Service runtime Docker, Dockerfile `./Dockerfile`, health check `/up`.
5. Chọn plan hỗ trợ Persistent Disk nếu cần giữ file lâu dài.
6. Web Service → Disks → Add Disk, mount path `/var/data`.
7. Thêm environment variables và `APP_KEY`; deploy. Container tự chạy `php artisan migrate --force`.
8. Nếu cần demo, đặt `RUN_SEED=true` đúng một lần, sau đó đổi lại `false` và redeploy.
9. Kiểm tra `/up`, `/login`, các role, avatar, check-in upload/camera, reports/CSV/print.
10. Redeploy và xác nhận avatar/proof còn tồn tại. Nếu không có Persistent Disk, bước này phải ghi nhận expected file-loss risk.

## File storage và quyền truy cập

File nằm dưới `UPLOAD_STORAGE_PATH`, mặc định local là `storage/app/private/uploads`; database chỉ lưu relative path. Proof không public và chỉ stream qua protected controller. Không chạy `storage:link` và không trả absolute path cho client.

## Compatibility và troubleshooting

Docker có cả `pdo_mysql` và `pdo_pgsql`; MySQL local/CI vẫn được giữ. Các dashboard/report query đã tránh biểu thức MySQL-specific. Lỗi DB: kiểm tra đúng Internal credentials và `DB_CONNECTION=pgsql`. Lỗi upload: kiểm tra Persistent Disk mount tại `/var/data` và quyền ghi. Camera cần HTTPS và quyền browser.
