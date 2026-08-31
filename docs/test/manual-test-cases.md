# Manual test cases

Phạm vi: Laravel 12, PHP 8.3, MySQL `hr_management_testing` cho automated test. Các bước UI thực hiện trên môi trường development đã seed dữ liệu demo; không dùng SQLite.

| ID | Role | Chức năng | Tiền điều kiện | Các bước | Kết quả mong đợi | Kết quả thực tế | PASS/FAIL | Minh chứng |
|---|---|---|---|---|---|---|---|---|
| MT-01 | Guest | Login hợp lệ | Có tài khoản | Mở `/login`, nhập email/mật khẩu đúng | Đăng nhập và chuyển dashboard đúng role | Automated PASS | PASS | `AuthenticationTest` |
| MT-02 | Guest | Login sai | Có tài khoản | Nhập mật khẩu sai | Hiện lỗi, không tạo session | Automated PASS | PASS | `AuthenticationTest` |
| MT-03 | Authenticated | Logout | Đã đăng nhập | Bấm **Đăng xuất** | POST logout, về trang chủ, session bị hủy | Automated PASS | PASS | `AuthenticationTest` |
| MT-04 | Admin/HR/Employee | Dashboard theo role | Có 3 role | Đăng nhập từng role | Mỗi role xem đúng dashboard | Automated PASS | PASS | `RoleAuthorizationTest` |
| MT-05 | Employee | Chặn khu vực HR | Employee đã đăng nhập | Truy cập `/hr/dashboard` | HTTP 403 | Automated PASS | PASS | `RoleAuthorizationTest` |
| MT-06 | HR | Chặn khu vực Admin | HR đã đăng nhập | Truy cập `/admin/dashboard` | HTTP 403 | Automated PASS | PASS | `RoleAuthorizationTest` |
| MT-07 | Admin | Khóa/mở tài khoản | Có user thường | Khóa user, thử login, mở lại | User bị chặn khi khóa; đăng nhập lại được khi mở | Automated PASS | PASS | `AdminUserManagementTest` |
| MT-08 | Admin | Quản lý tài khoản | Admin đã đăng nhập | Tạo HR và Employee | User tạo đúng role và trạng thái active | Automated PASS | PASS | `AdminUserManagementTest` |
| MT-09 | Admin/HR | CRUD phòng ban | Có quyền quản trị HR | Tạo, sửa, xem phòng ban | Dữ liệu lưu và hiển thị đúng | Automated PASS | PASS | `HrCrudTest` |
| MT-10 | Admin/HR | Validation phòng ban | Có phòng ban trùng mã | Gửi mã đã tồn tại | Hiện lỗi unique, dữ liệu cũ không đổi | Automated PASS | PASS | `HrCrudTest` |
| MT-11 | Admin/HR | Xóa phòng ban có nhân viên | Phòng ban có employee | Chọn xóa | Hệ thống từ chối xóa | Automated PASS | PASS | `HrCrudTest` |
| MT-12 | Admin/HR | Tạo nhân viên | Có phòng ban | Nhập form hợp lệ | Tạo đồng thời User role employee và Employee | Automated PASS | PASS | `HrCrudTest` |
| MT-13 | Admin/HR | Search/filter/pagination employee | Có nhiều employee | Lọc theo mã, phòng ban, trạng thái | Danh sách đúng và giữ query string | Automated PASS | PASS | `HrCrudTest` |
| MT-14 | Employee | Hồ sơ cá nhân | Employee đã đăng nhập | Mở hồ sơ của mình | Chỉ thấy dữ liệu của chính mình | Automated PASS | PASS | `AttendanceSelfServiceTest`, `ProfileTest` |
| MT-15 | Employee | Cập nhật trường an toàn | Có hồ sơ employee | Sửa phone, address, ngày sinh | Trường an toàn cập nhật; trường quản trị giữ nguyên | Automated PASS | PASS | `AttendanceSelfServiceTest` |
| MT-16 | Admin/HR | Chấm công | Có employee | Tạo/sửa bản ghi chấm công | Lưu đúng trạng thái và thời gian | Automated PASS | PASS | `AttendanceSelfServiceTest` |
| MT-17 | Admin/HR/Employee | Ownership chấm công | Có 2 employee | Employee thử xem dữ liệu người khác | Chỉ thấy lịch sử của mình; route HR bị chặn | Automated PASS | PASS | `AttendanceSelfServiceTest` |
| MT-18 | Admin/HR | Báo cáo, filter, CSV, print | Có attendance | Lọc báo cáo, export CSV, mở print | Aggregate đúng; CSV UTF-8 đúng tập lọc; print hoạt động | Automated PASS | PASS | `ReportsTest` |
| MT-19 | Admin/HR | AJAX mã employee và avatar | Có quyền HR/Admin | Kiểm tra mã, upload ảnh hợp lệ/không hợp lệ | JSON đúng; MIME/size được kiểm tra; file lưu public disk | Automated PASS | PASS | `SecurityPolishTest` |
| MT-20 | All | Responsive và migration/seed | App chạy, DB test riêng | Mở desktop/mobile; chạy migrate và seed dev/test | Layout không vỡ; migration/seed hoàn tất, không dùng SQLite | Checklist cần xác nhận trên browser; schema đã được test | PASS* | `DatabaseSchemaTest`, `RuntimeSmokeTest` |

`PASS*`: phần migration/schema có automated coverage; kiểm tra hiển thị mobile là checklist thủ công cần người chạy browser ghi ảnh chụp màn hình vào `docs/evidence/`.

## Automated coverage matrix

- Authentication, logout, locked account: `tests/Feature/Auth/AuthenticationTest.php`, `tests/Feature/RoleAuthorizationTest.php`.
- Authorization và 3 role: `tests/Feature/RoleAuthorizationTest.php`, `tests/Feature/AdminUserManagementTest.php`.
- Department/Employee CRUD: `tests/Feature/HrCrudTest.php`.
- Attendance/self-service/ownership: `tests/Feature/AttendanceSelfServiceTest.php`.
- Reports/statistics/CSV/print: `tests/Feature/ReportsTest.php`.
- AJAX availability/avatar upload: `tests/Feature/SecurityPolishTest.php`.
- Runtime pages/schema: `tests/Feature/RuntimeSmokeTest.php`, `tests/Feature/DatabaseSchemaTest.php`.
