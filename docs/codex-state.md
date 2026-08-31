STEP: 08
PROJECT_STATUS: Hoàn thành dashboard KPI, báo cáo thống kê, CSV và giao diện in; chưa triển khai Prompt 09.
LARAVEL_VERSION: 12.65.0
PHP_VERSION: Local 8.3.30; CI PHP 8.3.
CSS_STACK: Tailwind CSS 4 + Vite, giữ nguyên.
AUTH_STATUS: Laravel Breeze Blade; Authentication và protected routes đang hoạt động.
ROLE_STATUS: Admin/HR truy cập báo cáo; Employee bị chặn server-side bằng role middleware.
DATABASE_STATUS: Runtime MySQL 8.4.3 `hr_management`; test MySQL `hr_management_testing` tại 127.0.0.1:3306.
SEED_STATUS: Dữ liệu demo Prompt 04 được giữ nguyên.

COMPLETED:
- Đã resolve conflict sau stash pop, giữ cả routes Prompt 07 và Prompt 08.
- Dashboard HR: tổng nhân viên, active/inactive, phòng ban và attendance theo status tháng hiện tại.
- Dashboard Admin: tổng user, active/locked và user theo role, link HR module.
- ReportController dùng chung query filter cho HTML, CSV và print; không tạo bảng reports.
- Báo cáo filter tháng/năm/phòng ban/nhân viên/status chấm công/employment status; aggregate COUNT/GROUP BY.
- CSV stream download có UTF-8 BOM, header tiếng Việt và đúng tập dữ liệu sau filter.
- Blade print view có CSS @media print và window.print(), dùng browser Save as PDF.
- Eager loading employee.user/department, pagination, empty state, badge và filter/reset UI.
- Feature Tests KPI, aggregation/filter, CSV, print và authorization đạt.

FILES_CHANGED:
- routes/web.php
- docs/codex-state.md
- Các file Prompt 08 đã có trong working tree/staging: dashboard controllers/views, ReportController, report views, ReportsTest, docs/evidence/reports.md.

COMMANDS_RUN:
- git status; git diff --cc; git ls-files -u
- apply conflict resolution for routes/web.php and docs/codex-state.md

TEST_RESULTS: Conflict đã được resolve ở working tree; cần chạy lại route:list, view:cache và Feature Tests sau khi resolve.
OPEN_ISSUES:
- Chưa commit hoặc push; branch local và origin/feat/hr-reports-statistics vẫn diverged.
- Cần kiểm tra lại toàn bộ test sau merge trước khi commit.
- Prompt 09 chưa triển khai.
NEXT_STEP: VERIFY_AFTER_CONFLICT
