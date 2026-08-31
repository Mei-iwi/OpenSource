PROMPT 12 — README + REPORT + TEAMWORK + DEPLOYMENT DOCS

MỤC TIÊU
Hoàn thiện tài liệu cho 20% báo cáo và 10% teamwork. Không bịa minh chứng.

A. README.md
Phải có:
1. Tên đề tài.
2. Mục tiêu.
3. 3 role + permission matrix.
4. Chức năng.
5. Công nghệ môn học đã dùng.
6. MVC.
7. Database 4 bảng + relations.
8. Business rules.
9. Hướng dẫn cài local.
10. Tài khoản demo.
11. Chạy test.
12. Route/chức năng chính.
13. CSV/print.
14. AJAX/upload.
15. Git/teamwork.
16. Rubric mapping.
17. Hạn chế/hướng phát triển.
18. Deployment checklist.

B. CÀI LOCAL
Ghi lệnh:
git clone <GITLAB_REPOSITORY_URL>
cd <project>
composer install
copy .env.example .env
php artisan key:generate
tạo MySQL database
cấu hình DB_* trong .env
php artisan migrate --seed
php artisan storage:link
npm install && npm run build  # chỉ nếu project thực tế cần
php artisan serve

URL:
http://127.0.0.1:8000

Có thêm cú pháp cp .env.example .env cho Git Bash/Linux.
Không ghi credential thật.

C. REPORT OUTLINE
docs/report/report-outline.md:
Chương 1 Tổng quan
Chương 2 Phân tích yêu cầu + roles/use cases
Chương 3 Thiết kế MVC + ERD + DB
Chương 4 Cài đặt chức năng
Chương 5 Kiểm thử + kết quả + rubric
Chương 6 Kết luận/hạn chế/hướng phát triển
Phụ lục install/accounts/Git

Mỗi chương ghi:
- nội dung.
- diagram/screenshot.
- source minh chứng.

D. DIAGRAMS MERMAID
docs/diagrams/use-case.md
docs/diagrams/architecture.md
docs/diagrams/erd.md

Architecture:
Browser
-> Route/Middleware
-> Controller
-> FormRequest/Policy
-> Eloquent
-> MySQL
-> Blade Response

E. TEAMWORK DOC
docs/teamwork/task-assignment.md
docs/teamwork/meeting-log-template.md
docs/teamwork/git-evidence-guide.md

Không bịa tên/commit.
Task:
Thành viên | MSSV | Module | Branch | Commit/PR | Minh chứng

Git guide:
git log --oneline --decorate --graph --all
git shortlog -sn
git log --author="<name>" --oneline

F. DEPLOYMENT CHECKLIST
docs/report/deployment-checklist.md:
- APP_ENV=production
- APP_DEBUG=false
- production .env
- composer install --no-dev --optimize-autoloader
- php artisan migrate --force
- php artisan storage:link
- config/route/view cache nếu phù hợp
- document root public/
- storage/bootstrap/cache permissions
- HTTPS nếu có
- backup DB
- không commit .env

Cloud deploy không bắt buộc.

G. STATE
STEP: 12
DOC_STATUS:
NEXT_STEP: 13_FINAL_AUDIT

OUTPUT
- docs created.
- README completed.
- no fabricated evidence.
- READY FOR PROMPT 13.
