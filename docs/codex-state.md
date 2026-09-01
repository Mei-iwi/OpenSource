STEP: Prompt 12 - Documentation
PROJECT_STATUS: Prompt 01-12 completed; documentation conflict resolved locally; Prompt 13 not started.
LARAVEL_VERSION: 12.65.0
PHP_VERSION: Local 8.3.30; CI PHP 8.3.
CSS_STACK: Tailwind CSS 4 + Vite; Alpine.js mobile menu.
AUTH_STATUS: Laravel Breeze Blade; auth, account.active and role middleware active.
DATABASE_STATUS: Runtime MySQL 8.4.3 hr_management; tests use MySQL hr_management_testing at 127.0.0.1:3306.
FEATURE_STATUS: Authentication, 3 roles, User Management, Department/Employee CRUD, Attendance, Self-Service, Reports/CSV/Print, AJAX availability and avatar upload complete.
UI_STATUS: Shared layout/navbar/sidebar, active role navigation, responsive menu/tables/forms, badges, flash, empty states and print presentation complete.
SECURITY_STATUS: Server-side role/ownership authorization, CSRF, Form Requests, protected fields and upload validation enabled.
PROMPT_10: Test documentation complete; 61 tests, 222 assertions, 0 failures.
PROMPT_11: UI polish and rubric evidence documents complete; screenshots pending manual capture.
PROMPT_12: Documentation aligned with migrations, routes, Seeder, tests and workflow.

DOCUMENTATION_FILES:
- README.md
- docs/architecture.md
- docs/diagrams/erd.md
- docs/report-outline.md
- docs/requirements-matrix.md
- docs/test/manual-test-cases.md
- docs/test/bug-log.md
- docs/test/test-matrix.md
- docs/evidence/README.md
- docs/evidence/how-to-capture.md
- docs/evidence/database.md
- docs/evidence/reports.md
- docs/evidence/runtime-smoke-test.md
- docs/evidence/ui-rubric.md
- docs/evidence/screenshot-checklist.md
- docs/deployment.md
- docs/teamwork/contribution.md
- docs/teamwork/git-evidence.md

COMMANDS_RUN:
- composer validate
- php artisan optimize:clear
- php artisan route:list
- php artisan view:clear
- php artisan view:cache
- DB_CONNECTION=mysql DB_DATABASE=hr_management_testing php artisan test
- cmd.exe /c npm run build
- git status; git diff --name-only --diff-filter=U; git diff --check

TEST_RESULTS: 61 tests, 222 assertions, 0 failures on MySQL hr_management_testing.
BUILD_RESULTS: composer validate PASS; route:list PASS (61 routes); view:clear PASS; view:cache PASS; npm build PASS.
OPEN_ISSUES: Manual screenshots and verified team member/PR details pending; no runtime or test failures.
NEXT_STEP: Prompt 13
PROMPT_13_STEP: Prompt 13 - Final Audit
ENVIRONMENT: PHP 8.3.30; Laravel 12.65.0; Composer 2.9.4; Node 22.17.1; npm 10.9.2 via cmd.exe.
DATABASE: Runtime hr_management; test database hr_management_testing; MySQL 8.4.3; migrations all ran.
AUTH: Laravel Breeze Blade login/logout/password flows and public registration remain available as implemented.
AUTHORIZATION: Admin/HR/Employee middleware and ownership checks verified; Employee blocked from Admin/HR modules.
MODULE_AUDIT: Authentication, User Management, Departments, Employees, Attendance, Self-Service, Reports, CSV, Print, AJAX and Avatar audited.
SECURITY_AUDIT: .env untracked/not tracked; no tracked runtime uploads; no user-controlled {!! !!}; no debug dump artifacts; CSRF/Form Requests/IDOR checks retained.
ROUTE_AUDIT: 61 routes listed; no preview routes; main route smoke tests pass.
UI_AUDIT: Shared Blade layout, role navigation, responsive menu/tables/forms, badges, empty states and print presentation audited.
DOCUMENTATION_AUDIT: README, architecture, ERD, report outline, requirements matrix, tests, evidence, teamwork, deployment, demo and submission docs aligned.
CI_AUDIT: GitHub Actions retains PHP 8.3, MySQL 8.4, pdo_mysql, mbstring, hr_management_testing, Composer install, npm ci, npm build and tests.
TEST_RESULTS_PROMPT_13: MySQL hr_management_testing; 61 tests, 222 assertions, 0 failures.
BUILD_RESULTS_PROMPT_13: composer validate PASS; route:list PASS; view:clear PASS; view:cache PASS; npm build PASS via cmd.exe.
PINT_RESULT_PROMPT_13: OPTIONAL CHECK FAIL due existing broad formatting/line-ending issues across legacy files; no project-wide formatting applied.
DEMO_CHECKLIST: docs/demo-checklist.md
SUBMISSION_CHECKLIST: docs/submission-checklist.md
PRODUCTION_FIXES: none; no production bug found during final audit.
OPEN_ISSUES_PROMPT_13: Real screenshots, verified teamwork details and post-push CI confirmation remain manual actions.
MANUAL_ACTIONS_REQUIRED: Capture real screenshots; fill verified team contribution data; verify final GitHub Actions run; prepare report Word/PDF if required.
FINAL_STATUS: Source/runtime/security audit PASS; ready for final human demo/submission actions.

STEP: Prompt 14 - Vietnamese Localization
UI_LANGUAGE: Vietnamese for shared layout, navigation, dashboards, authentication, profile and module screens
VALIDATION_LANGUAGE: Vietnamese `lang/vi/validation.php` with translated attributes and common rules
FLASH_MESSAGES: Existing status codes preserved; authentication/password translations added
AUTH_LANGUAGE: Vietnamese labels/messages for login, registration, password reset, verification and logout
REPORT_LANGUAGE: Vietnamese report labels and status badges; CSV/print identifiers preserved
CSV_LANGUAGE: Vietnamese headers/content labels with UTF-8 handling preserved
README_LANGUAGE: Vietnamese
DOCS_LANGUAGE: Vietnamese headings and project-facing documentation; technical names preserved
TECHNICAL_IDENTIFIERS_PRESERVED: Routes, controller/model names, role/status values, database columns, environment variables and commands
FILES_CHANGED: README.md, config/app.php, .env.example, lang/vi.json, lang/vi/*.php, resources/views/auth, profile, dashboard, welcome, layouts/navigation, partials/sidebar, components/status-badge, selected docs/*.md
TEST_RESULTS: 61 tests, 222 assertions, 0 failures on MySQL hr_management_testing
BUILD_RESULTS: composer validate PASS; optimize:clear PASS; route:list PASS (61 routes); view:clear PASS; view:cache PASS; npm run build PASS
ENGLISH_UI_STRINGS_REMAINING: No known active English navigation/auth labels; technical terms such as Email, HR, CSV, PDF and Blade remain where appropriate
OPEN_ISSUES: Real screenshots, verified teamwork details and post-push CI confirmation remain manual actions
MANUAL_ACTIONS_REQUIRED: Capture real screenshots and complete verified team/report submission details
FINAL_STATUS: Vietnamese localization PASS; no business, schema, route, authz or CI changes made

STEP: UI Redesign A
UI_FOUNDATION: Chuẩn hóa nền slate sáng, màu nhấn cam, card/border/spacing; bổ sung dark mode cơ bản
LOGIN: Card responsive, nhận diện Quản lý nhân sự, tiêu đề chào mừng, form tiếng Việt và lỗi rõ ràng
SIDEBAR: Desktop expanded/collapsed 72/288px, icon/title tooltip, localStorage; mobile drawer/backdrop hiện có được giữ nguyên
THEME: Light/dark mode bằng Tailwind class và localStorage `hr-theme`; không thêm package
FILES_CHANGED: tailwind.config.js, resources/css/app.css, resources/js/app.js, layouts/app.blade.php, layouts/guest.blade.php, partials/navbar.blade.php, partials/sidebar.blade.php, auth/login.blade.php, dashboard views, lang/vi, selected docs
TEST_RESULTS: Auth/runtime targeted 21 tests, 71 assertions, 0 failures trên MySQL hr_management_testing
OPEN_ISSUES: Chưa chạy browser thủ công ở 375px/768px/1280px; accent switching chưa triển khai
NEXT_STEP: Prompt B

UI_LOGIN_ADJUSTMENT: Đã bỏ hoàn toàn application logo khỏi guest/login; nền login dùng gradient CSS sáng amber/orange/cyan, không dùng ảnh nền; card và CTA dùng màu cam tươi.
UI_LOGIN_CHECK: Auth/Runtime 7 tests, 41 assertions, 0 failures; view clear/cache, npm build và git diff --check PASS. Một lượt chạy song song trước đó gặp Windows file-lock khi compile view, đã chạy lại tuần tự thành công.
UI_LOGIN_COLOR_UPDATE: Nền xanh blue sáng hơn; form mở rộng max-w-xl, căn giữa cân đối; viền gradient blue/sky/cyan; CTA xanh đậm chữ trắng; thêm họa tiết hoa/lá SVG inline, không dùng background image.
UI_LOGIN_UPDATE_CHECK: AuthenticationTest 5 tests, 12 assertions, 0 failures; view:clear, view:cache và npm run build PASS.
UI_LOGIN_BRIGHT_BLUE_UPDATE: Khung login dùng nền xanh sáng, nhãn Email/Mật khẩu/Ghi nhớ đăng nhập màu trắng, input nền trắng và viền gradient conic-gradient chuyển động quanh form bằng CSS animation.
UI_LOGIN_BRIGHT_BLUE_CHECK: AuthenticationTest 5 tests, 12 assertions, 0 failures; view clear/cache, npm build và git diff --check PASS.
UI_LOGIN_LED_BORDER_UPDATE: Form được cố định; chỉ pseudo-element viền gradient conic-gradient xoay như LED quanh khung, nội dung không chuyển động.
UI_GLOBAL_POLISH: Nền các trang chuyển sang sky-50/gradient sáng; sidebar collapsed chỉ hiện icon và title tooltip, có transition width/ease mượt; KPI dashboard có hover nhẹ và mũi tên nối dạng luồng ở màn hình lớn.
LOGIN_DIAGNOSTIC: Runtime database `hr_management` bị thiếu dữ liệu users (0 user), không phải lỗi Authentication. Đã chạy `php artisan db:seed --class=DatabaseSeeder` trên database development đã xác nhận; hiện có 15 users active, gồm admin@example.com, hr@example.com, hr2@example.com và employee1@example.com.
LOGIN_CHECK: AuthenticationTest và RoleAuthorizationTest PASS; tài khoản demo dùng mật khẩu `Password123!` theo DatabaseSeeder.
LOGIN_ROOT_CAUSE: phpunit.xml thiếu cấu hình database test nên RefreshDatabase đã dùng nhầm `hr_management`, làm sạch users runtime. Đã bổ sung MySQL `hr_management_testing` vào phpunit.xml.
LOGOUT_419_CHECK: Đã xóa session runtime cũ trong `hr_management.sessions` và chạy `php artisan optimize:clear`; Authentication/Role tests 12 passed, 30 assertions.
EMPLOYEE_403_REGRESSION_CHECK: Bản sửa redirect ban đầu dùng nhầm named parameter `absolute` cho `redirect()->route()` và gây 500; đã sửa thành `redirect()->route('dashboard')`.
LOGIN_RECOVERY: Đã seed lại database development `hr_management`; hiện có 15 users, tài khoản demo active. Auth/Role tests 11 passed, 23 assertions; runtime còn 15 users sau test.
CSRF_419_RECHECK: Sau `optimize:clear`, curl tới cả `http://localhost:8000/login` và `http://127.0.0.1:8000/login` đều trả 200 và phát hành đủ `XSRF-TOKEN`/`laravel_session`; 419 phát sinh khi client gửi POST không kèm token/session tương ứng, cần dùng một hostname nhất quán và tải lại trang login.
EMPLOYEE_403_FIX: Login không còn dùng `redirect()->intended()`; luôn đi qua `/dashboard` để phân giải dashboard theo role, tránh Employee quay lại URL Admin/HR bị cấm trong session.
THEME_CONTRAST_UPDATE: Theme tối dùng nền xanh sáng tương phản, surface/card xanh blue, chữ sáng, input và border riêng; bổ sung transition màu 240ms và nút chuyển đổi hiển thị `Sáng/Tối` rõ ràng.
PROFILE_ALL_ROLES_UPDATE: Hồ sơ chung của Admin/HR hỗ trợ upload avatar vào `storage/app/public/avatars`, validate MIME/size, xóa ảnh cũ và hiển thị ảnh sau cập nhật; Employee tiếp tục dùng luồng avatar ownership hiện có.
FIXED_SHELL_UPDATE: Header sticky, sidebar desktop cố định theo chiều cao màn hình và chỉ vùng main overflow-y-auto; nội dung dài cuộn bên dưới header.
PROFILE_SHELL_CHECK: Migration avatar PASS; Profile/Attendance/Runtime 12 tests, 72 assertions, 0 failures; view cache, npm build và git diff --check PASS.
AVATAR_URL_FIX: Public disk dùng URL root-relative `/storage`, tránh ghép `APP_URL` thiếu port/khác hostname; symbolic link `public/storage` tồn tại và ảnh đã lưu trong `storage/app/public/avatars`.
LOGOUT_REDIRECT_UPDATE: Logout vẫn POST/CSRF và invalidate session, nhưng redirect về route `login` thay vì `/` để không hiện trang mặc định Laravel “Let's get started”.
CSRF_419_DIAGNOSTIC: Đã xác nhận APP_KEY hợp lệ, session database `hr_management.sessions` hoạt động, GET `/login` trả 200 và phát hành XSRF-TOKEN/laravel_session; optimize:clear đã chạy. AuthenticationTest 5/12 PASS. Không phát hiện lỗi source.

STEP: UI-01
LAYOUT: Đã chuẩn hóa nền/surface/border bằng CSS variables; main co giãn theo sidebar, header sticky và vùng nội dung cuộn độc lập.
SIDEBAR: Desktop expanded/collapsed 288/88px, icon + label, active màu cam, tooltip/title/aria-label, localStorage; mobile drawer/backdrop hiện có được giữ nguyên.
TOPBAR: Bổ sung page title/breadcrumb ngắn, avatar/ảnh đại diện, họ tên, role tiếng Việt, dropdown hồ sơ và đăng xuất; giữ theme sáng/tối.
FILES_CHANGED: resources/css/app.css, resources/views/layouts/app.blade.php, resources/views/partials/navbar.blade.php, resources/views/partials/sidebar.blade.php, docs/codex-state.md
COMMANDS_RUN: php artisan view:clear; php artisan view:cache; cmd.exe /c npm run build; php artisan test tests/Feature/RuntimeSmokeTest.php tests/Feature/Auth/AuthenticationTest.php; git diff --check
TEST_RESULTS: 8 tests, 48 assertions, 0 failures trên MySQL hr_management_testing; view cache PASS; npm build PASS.
OPEN_ISSUES: Chưa kiểm tra browser thủ công tại 375px/768px/1280px; không phát hiện lỗi compile hoặc runtime qua targeted tests.
NEXT_STEP: UI-02

STEP: UI-02
LOGIN: Đã làm mới giao diện login responsive, heading/brand rõ ràng, validation và auth error dễ đọc, show/hide password bằng Alpine, giữ remember me và không hiển thị demo credentials.
LOGOUT_CONFIRM: Đã bổ sung modal xác nhận với nút Hủy/Đăng xuất; logout vẫn dùng POST + CSRF và giữ route hiện tại.
THEME: Light/Dark dùng localStorage `hr-theme`, đọc theme trước Alpine khởi động; guest/login và app layout đều hỗ trợ nền, card, form, modal tương phản.
ACCESSIBILITY: Label rõ, focus-visible, aria-label, aria-live cho lỗi đăng nhập, aria-modal/aria-labelledby cho modal, button có nội dung dễ hiểu.
FILES_CHANGED: resources/views/auth/login.blade.php, resources/views/layouts/guest.blade.php, resources/views/partials/navbar.blade.php, resources/css/app.css, resources/js/app.js, docs/codex-state.md
TEST_RESULTS: 6 tests, 19 assertions, 0 failures trên MySQL hr_management_testing; view:clear PASS; view:cache PASS; npm run build PASS; git diff --check PASS.
OPEN_ISSUES: Chưa kiểm tra browser thủ công tại 375px/768px/1280px; không phát hiện lỗi auth/view/build trong targeted checks.
NEXT_STEP: UI-03

STEP: UI-03
DASHBOARD_ADMIN: Đã nâng cấp dashboard Admin với 6 KPI nhân sự/chấm công, bộ lọc tháng/năm, tài khoản theo role, chấm công gần đây và quick link báo cáo.
DASHBOARD_HR: Đã nâng cấp dashboard HR tập trung vào nhân sự, chấm công, phòng ban, chuyên cần, quick links và bảng chấm công gần đây; không hiển thị quản trị tài khoản.
KPI: Tổng nhân viên, đang làm việc, ngừng làm việc, có mặt hôm nay, đi muộn hôm nay, vắng hôm nay; dữ liệu aggregate từ MySQL.
CHARTS: Thêm duy nhất Chart.js; tối đa 3 chart gồm doughnut trạng thái chấm công, bar nhân sự theo phòng ban và line xu hướng chuyên cần 6 tháng; có legend tiếng Việt/empty state.
QUERY_OPTIMIZATION: Dùng COUNT/GROUP BY, conditional date range, YEAR/MONTH aggregate và eager loading Attendance -> Employee -> User/Department; không query trong loop.
DARK_MODE: Chart colors, legend, grid, panel, table và form tương thích Light/Dark tại thời điểm render; theme vẫn dùng localStorage.
FILES_CHANGED: app/Http/Controllers/AdminDashboardController.php, app/Http/Controllers/HrDashboardController.php, resources/views/dashboard/admin.blade.php, resources/views/dashboard/hr.blade.php, resources/css/app.css, resources/js/app.js, package.json, package-lock.json, docs/codex-state.md
TEST_RESULTS: Targeted 12 tests, 64 assertions, 0 failures; full suite 62 tests, 229 assertions, 0 failures trên MySQL hr_management_testing.
BUILD_RESULTS: php artisan view:clear PASS; php artisan view:cache PASS; npm run build PASS; git diff --check PASS.
OPEN_ISSUES: Chưa kiểm tra browser thủ công tại 375px/768px/1280px; chưa kiểm tra chuyển theme khi chart đang hiển thị.
NEXT_STEP: UI-04

COMBINED_SECTION_1: PASS - UI-03 dashboard Admin/HR, KPI, Chart.js, aggregate query và authorization đã được xác nhận.
COMBINED_SECTION_2: PASS - Employee dashboard dùng dữ liệu Attendance thật, profile/avatar có placeholder, preview local và field protection giữ nguyên.
SECTION_2_TESTS: 10 tests, 69 assertions, 0 failures; view cache, npm build và runtime smoke PASS.
SECTION_2_OPEN_ISSUES: Chưa kiểm tra browser thủ công tại 375px/768px/1280px.
COMBINED_NEXT_SECTION: SECTION 3 - UI-05 CRUD/Table/Form/Filter/Modal polish
COMBINED_SECTION_3: PASS - CRUD tables, filters, forms, badges và action styling đã polish bằng CSS dùng chung; backend semantics giữ nguyên.
SECTION_3_TESTS: 18 tests, 70 assertions, 0 failures; view cache, npm build và diff check PASS.
SECTION_3_OPEN_ISSUES: Các action nguy hiểm hiện vẫn dùng confirm native ở một số màn hình legacy; không phát sinh lỗi runtime.
COMBINED_NEXT_SECTION: SECTION 4 - UI-06 Attendance/Reports/CSV/Print UX
COMBINED_SECTION_4: PASS - Attendance KPI/filter/table và Reports heading/chart/CSV/Print UX đã hoàn thiện; backend filter giữ nguyên.
SECTION_4_TESTS: 15 tests, 57 assertions, 0 failures; view cache, npm build và diff check PASS.
SECTION_4_OPEN_ISSUES: Chưa kiểm tra browser thủ công chức năng Print ở nhiều kích thước.
COMBINED_NEXT_SECTION: SECTION 5 - UI-07 Leave Request tối giản
COMBINED_SECTION_5: PASS - Leave Request độc lập với Attendance, employee create/list/detail/cancel pending, HR/Admin review pending và overlap validation đã hoàn thành.
SECTION_5_TESTS: 10 leave tests, 35 assertions; full suite 72 tests, 264 assertions, 0 failures trên MySQL hr_management_testing.
SECTION_5_OPEN_ISSUES: Chưa kiểm tra browser thủ công flow đơn nghỉ; không phát hiện lỗi automated.
COMBINED_NEXT_SECTION: SECTION 6 - UI-08 Final UI/UX regression + docs
STEP: Combined UI-03 to UI-08
DASHBOARD_ADMIN_HR: Dashboard Admin/HR đã có KPI thật, Chart.js tối đa 3 biểu đồ, filter tháng/năm, pending leave count và quick links.
EMPLOYEE_DASHBOARD: Employee dashboard dùng aggregate tháng thật, hồ sơ riêng, KPI chấm công, chỉ số khách quan và 7 bản ghi gần nhất.
PROFILE: Profile UI hiển thị đầy đủ thông tin read-only/editable theo ownership; avatar có placeholder và local preview.
AVATAR: Giữ Storage public, validation MIME/size và ownership; tests avatar PASS.
CRUD_UI: Chuẩn hóa style table/form/filter/badge/button responsive bằng CSS dùng chung; giữ backend semantics.
ATTENDANCE_UI: Attendance có filter, KPI status, table responsive và empty state.
REPORT_UI: Reports có tiêu đề, summary, chart status, filter, CSV UTF-8 và Print/Save PDF.
CHARTS: Chart.js dùng dữ liệu COUNT/GROUP BY MySQL, legend tiếng Việt và màu tương thích dark mode.
LEAVE_REQUEST: Đã thêm migration/model/factory, employee create/list/detail/cancel pending, HR/Admin list/detail approve/reject, overlap validation, ownership và dashboard links.
SIDEBAR: Bổ sung menu Đơn nghỉ đúng role, giữ expanded/collapsed/mobile drawer và active state.
THEME: Light/Dark localStorage giữ nguyên; panel/table/form/chart/modal có style dark tương thích.
AUTH_UI: Login/show-hide password/remember/error và logout confirm POST+CSRF giữ nguyên sau regression.
SECURITY: Middleware role, ownership, Form Request, CSRF, mass assignment và leave review authorization được giữ/kiểm tra.
TEST_RESULTS: Full suite 72 tests, 264 assertions, 0 failures trên MySQL hr_management_testing.
BUILD_RESULTS: composer validate, optimize:clear, route:list 69 routes, view:clear, view:cache, npm run build và git diff --check PASS.
FILES_CHANGED: Dashboard/controllers/views, Employee profile, Attendance/Reports views/controller, Leave Request source/tests/migration/factory/views, shared CSS/sidebar, README/docs/diagrams/checklists.
MANUAL_ACTIONS_REQUIRED: Chụp screenshot thật theo docs/evidence/screenshot-checklist.md; kiểm tra browser các viewport 375px/768px/1280px, theme/chart và flow leave; xác minh CI sau push.
OPEN_ISSUES: Không có lỗi automated; pending manual browser screenshots/CI verification.
FINAL_STATUS: Combined UI-03 đến UI-08 automated regression PASS; chưa commit/push.

STEP: ATT-01
ATTENDANCE_MODEL: Attendance thuộc Employee qua employee_id; User -> Employee -> Attendances; giữ unique employee_id + work_date và status present/late/absent/leave.
ROLE_SELF_ATTENDANCE: Admin/HR/Employee chỉ đủ điều kiện tự chấm công khi có Employee profile; ownership server-side; ATT-01 chưa thêm route/check-in/check-out.
ADMIN_EMPLOYEE_PROFILE: Seeder tạo profile ADM-0001 cho admin@example.com.
HR_EMPLOYEE_PROFILE: Seeder tạo profile HR-0001/HR-0002 cho hr@example.com/hr2@example.com.
PHOTO_SCHEMA: Đã thêm nullable check_in_photo_path, check_out_photo_path, check_in_method, check_out_method; method dự kiến camera/upload; không lưu base64/raw binary/biometric.
PRIVATE_STORAGE_DECISION: ATT-02 sẽ lưu proof ở private storage theo logical path attendance-proofs/{employee_id}/{year}/{month}; không dùng public URL như avatar.
FILES_CHANGED: database/migrations/2026_09_01_130000_add_proof_fields_to_attendances_table.php, app/Models/Attendance.php, database/seeders/DatabaseSeeder.php, tests/Feature/AttendanceAuditTest.php, docs/codex-state.md
TEST_RESULTS: composer validate PASS; php artisan migrate:status PASS (10 migrations Ran); php artisan test --filter=Attendance PASS (10 tests, 39 assertions); git diff --check PASS.
OPEN_ISSUES: Chưa triển khai camera/upload/check-in/check-out; database dev hiện đã migrate proof columns nhưng chưa reseed các demo profile trong DB hiện tại.
NEXT_STEP: ATT-02

STEP: ATT-02
SELF_ATTENDANCE: ÄÃ£ triá»ƒn khai page "Cháº¥m cÃ´ng cá»§a tÃ´i" vÃ  route dÃ¹ng chung cho Admin/HR/Employee cÃ³ Employee profile.
ROUTES: GET me/attendance; POST me/attendance/check-in; POST me/attendance/check-out; auth + account.active; khÃ´ng nháº­n employee_id Ä‘á»ƒ xÃ¡c Ä‘á»‹nh ownership.
CHECK_IN_OUT: Check-in táº¡o duy nháº¥t attendance ngÃ y hiá»‡n táº¡i; check-out yÃªu cáº§u check-in trÆ°á»›c vÃ  khÃ´ng cho submit láº¡i; server vÃ  unique constraint cÃ¹ng báº£o vá»‡ duplicate.
CAMERA_UPLOAD: Modal chá»n Chụp ảnh/Tải ảnh lên; camera dÃ¹ng MediaDevices getUserMedia video-only, capture frame, preview, stop tracks; upload lÃ  fallback khi camera khÃ´ng há»— trá»£/bá»‹ tá»« chá»‘i.
PHOTO_STORAGE: Storage local private disk storage/app/private/attendance-proofs/employee-{id}/YYYY/MM; database chá»‰ lÆ°u path vÃ  method camera/upload; khÃ´ng public URL, base64, raw binary hay biometric.
VALIDATION: SelfAttendanceRequest báº¯t buá»™c image, mimes jpeg/jpg/png/webp, max 3072KB, method camera/upload; filename Ä‘Æ°á»£c sinh ngáº«u nhiÃªn phÃ­a server.
FILES_CHANGED: app/Http/Controllers/SelfAttendanceController.php, app/Http/Requests/SelfAttendanceRequest.php, resources/views/attendance/self.blade.php, routes/web.php, resources/views/partials/sidebar.blade.php, tests/Feature/SelfAttendanceTest.php, docs/codex-state.md
TEST_RESULTS: php artisan test --filter=SelfAttendance PASS (8 tests, 36 assertions); full php artisan test PASS (84 tests, 316 assertions) trÃªn MySQL hr_management_testing; php artisan view:cache PASS; npm run build PASS; git diff --check PASS.
OPEN_ISSUES: ChÆ°a kiá»ƒm tra webcam thá»±c táº¿ trÃªn trÃ¬nh duyá»‡t/thiáº¿t bá»‹; cÃ¡c test camera PHPUnit Ä‘Æ°á»£c thay báº±ng backend upload/method camera, theo yÃªu cáº§u khÃ´ng test webcam tháº­t.
NEXT_STEP: ATT-03

STEP: ATT-03
ATTENDANCE_MANAGEMENT: HR/Admin attendance list now shows compact proof indicators and check-in/check-out methods; manual attendance remains valid without photos.
PROOF_ENDPOINT: GET /attendance/{attendance}/proof/{type}, type check-in/check-out; streams inline from local private storage without exposing storage path.
AUTHORIZATION: Admin/HR can view managed attendance proofs; Employee can view only own attendance proof; arbitrary ownership and invalid proof types return 403/404.
EMPLOYEE_HISTORY: Employee attendance history shows check-in/out methods and links to own available proof images; missing proof renders safely.
PRIVATE_STORAGE: Proof files remain under storage/app/private and are not exposed through public/storage symlink.
FILES_CHANGED: app/Http/Controllers/AttendanceProofController.php, routes/web.php, resources/views/hr/attendances/index.blade.php, resources/views/employee/attendances/index.blade.php, tests/Feature/AttendanceProofTest.php, docs/codex-state.md
TEST_RESULTS: php artisan test --filter=Attendance PASS (22 tests, 87 assertions); full php artisan test PASS (88 tests, 328 assertions) on MySQL hr_management_testing; php artisan view:cache PASS; npm run build PASS; git diff --check PASS.
OPEN_ISSUES: No automated issues; browser/manual verification of private image display remains a manual action.
NEXT_STEP: ATT-04

STEP: ATT-04
HEADER_CLEANUP: Topbar now has one contextual SVG module icon, page title, avatar/name/role and focused user dropdown; decorative and duplicate icon clutter was reduced.
USER_AREA: Dropdown includes profile, theme preference, navigation position/state controls and POST/CSRF logout confirmation.
NAV_STATE: expanded/collapsed/hidden stored in localStorage key hr-nav-state; legacy collapsed key remains synchronized; hidden mode always exposes a reopen button.
NAV_POSITION: left/right/top/bottom stored in localStorage key hr-nav-position; layout changes shell direction/flow rather than only transforming the sidebar.
RESPONSIVE: Left/right remain drawer-based on mobile; top/bottom use horizontal overflow; bottom navigation includes safe-area padding and content remains scrollable.
TRANSITIONS: Navigation width/height/transform/opacity transitions target 200-300ms; prefers-reduced-motion disables nonessential motion.
ICON_CLEANUP: Shared page header has one functional contextual SVG icon; existing module/status icons and accessibility controls retained.
FILES_CHANGED: resources/views/layouts/app.blade.php, resources/views/partials/navbar.blade.php, resources/views/partials/sidebar.blade.php, resources/views/components/page-header.blade.php, resources/css/app.css, docs/codex-state.md
TEST_RESULTS: Full php artisan test PASS (88 tests, 328 assertions); php artisan view:cache PASS; npm run build PASS; git diff --check PASS.
OPEN_ISSUES: Browser verification of all four navigation positions and reduced-motion preference remains a manual action; no automated/runtime errors.
NEXT_STEP: ATT-05

STEP: ATT-05 Final
SELF_ATTENDANCE: Automated coverage confirms Employee, HR and Admin with Employee profiles can check in/out through shared self-attendance routes; missing profiles return friendly 403 instead of 500.
CAMERA: Browser implementation opens only after click, captures a still frame, supports retake/preview, stops MediaStream tracks and falls back to upload on unsupported/denied camera; real webcam smoke remains manual.
UPLOAD: Server requires image jpeg/jpg/png/webp up to 3072KB, uses random server filename and private local storage; invalid and oversized files rejected.
PRIVATE_PHOTO: Proof files remain on local private disk; protected stream endpoint enforces Admin/HR management access and Employee ownership; no public attendance proof URL, face recognition or biometric processing.
ADMIN_HR_ATTENDANCE: Existing manual HR/Admin CRUD remains functional and manual records may have nullable proof fields.
HEADER: Single contextual SVG icon + page title on left; avatar/name/role dropdown on right; settings and logout remain in user area.
NAV_POSITION: left/right/top/bottom implemented with hr-nav-position localStorage and real shell flow changes; responsive horizontal overflow/bottom safe-area rules added.
NAV_STATE: expanded/collapsed/hidden implemented with hr-nav-state localStorage; hidden mode has reopen control and legacy collapsed preference is synchronized.
ANIMATION: 200-300ms navigation transitions and prefers-reduced-motion override present.
RESPONSIVE: CSS covers 375px/768px/1280px+ layouts, but real browser verification is still required for modal/table/menu edge cases.
SECURITY: CSRF/Form Requests, role middleware, ownership/IDOR checks, mass assignment, MIME/size validation, private storage and no arbitrary employee_id selection audited.
TEST_RESULTS: composer validate PASS; optimize:clear PASS; route:list PASS (73 routes); view:clear/view:cache PASS; full php artisan test PASS (88 tests, 328 assertions) on MySQL hr_management_testing; git diff --check PASS.
BUILD_RESULTS: npm run build PASS (Vite 6.4.3).
FILES_CHANGED: README.md, docs/architecture.md, docs/requirements-matrix.md, docs/demo-checklist.md, docs/evidence/screenshot-checklist.md, docs/codex-state.md.
MANUAL_ACTIONS_REQUIRED: Capture real browser screenshots listed in docs/evidence/screenshot-checklist.md; verify camera permission/retake/track-stop, navigation positions/states, 375px/768px/1280px+, dark mode and no content overlap.
OPEN_ISSUES: Manual browser and real-device evidence pending; no automated/runtime/build errors.
FINAL_STATUS: Automated self-attendance, privacy, authorization, header/navigation regression PASS; final human visual/device evidence remains pending.

UI_LAYOUT_ADJUSTMENT: Menu top sticky below header; menu bottom fixed to viewport with content padding/safe area; left/right collapsed sidebar hides horizontal overflow and uses equal icon cells.
UI_HEADER_ADJUSTMENT: User area now has orange bordered avatar/name/role trigger; logout dialog is fixed to the full viewport and centered.
UI_ADJUSTMENT_CHECK: view clear/cache PASS; npm run build PASS; git diff --check PASS.
LAYOUT_SCROLL_FIX: Header/taskbar is explicitly sticky at viewport top; top navigation stays below it and bottom navigation remains fixed with content clearance.
EMPLOYEE_DASHBOARD_LAYOUT: Employee KPI cards now use a balanced three-column desktop grid; recent attendance and attendance indicators share equal-width columns with min-width safeguards.
AVATAR_HEADER_FIX: User now exposes avatar_url from User avatar_path with Employee avatar_path fallback, so the authenticated topbar renders the uploaded avatar instead of the initial letter.
COLLAPSED_SCROLL_FIX: Collapsed navigation removes main max-width/margins and hides horizontal overflow on the desktop nav, keeping the scrollbar aligned to the content edge.
LATEST_UI_CHECK: Profile/Security/Authentication targeted tests 14 passed, 58 assertions; view clear/cache PASS; npm run build PASS; git diff --check PASS.
FIXED_TOP_BOTTOM_NAV: In top/bottom modes header is fixed to viewport; top navigation is fixed below header and workspace/main receive compensating spacing so content remains visible while scrolling.
FIXED_NAV_CHECK: view clear/cache PASS; npm run build PASS; git diff --check PASS.
DASHBOARD_GRID_ADJUSTMENT: Admin/HR/Employee dashboard grids use md breakpoints for balanced two/three-column distribution at zoomed desktop widths while retaining one-column mobile layout.
DASHBOARD_GRID_CHECK: Reports/Role/Runtime targeted tests 12 passed, 64 assertions; view clear/cache PASS; npm run build PASS; git diff --check PASS.
DASHBOARD_GRID_BREAKPOINT_FIX: HR/Admin chart and quick-link sections now use sm two-column grids; Employee lower dashboard section also uses sm two-column grid so zoomed desktop layouts do not collapse into a left-only stack.
DASHBOARD_TWO_COLUMN_FIX: Added explicit dashboard-two-column grid (2 equal columns by default, 1 column below 640px) and min-width safeguards to remove unexplained right-side whitespace.
DASHBOARD_TWO_COLUMN_CHECK: view clear/cache PASS; npm run build PASS; git diff --check PASS.
DASHBOARD_FLEX_DISTRIBUTION_FIX: Dashboard two-panel sections now use explicit equal flex columns with min-width 0; below 640px they stack to one column. This prevents the second panel from dropping under the first at the current viewport/zoom.
DASHBOARD_FLEX_CHECK: view clear/cache PASS; npm run build PASS; git diff --check PASS.
RUNTIME_ROLE_PROFILE_FIX: Development database hr_management had Admin/HR users without Employee rows; inserted missing profiles conditionally for admin@example.com (ADM-0001), hr@example.com (HR-0001) and hr2@example.com (HR-0002), matching the Seeder mapping.
RUNTIME_ROLE_PROFILE_CHECK: SelfAttendance 8 tests/36 assertions PASS; AttendanceProof 4 tests/12 assertions PASS; view:cache PASS; git diff --check PASS.
DASHBOARD_WIDTH_FIX: app-main now uses full available workspace width without max-width/margin compression; dashboard two-column panels can occupy both sides.
NAV_EXPAND_CONTROL_FIX: Header includes an explicit Mở rộng menu/Thu gọn menu control, synchronized with hr-nav-state and localStorage, including when sidebar content is not visible.
DASHBOARD_WIDTH_CHECK: Reports/Role/Runtime targeted tests 12 passed, 64 assertions; view clear/cache PASS; npm run build PASS; git diff --check PASS.
