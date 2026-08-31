# Minh chứng Database — Prompt 04

- Bảng chính: `users`, `departments`, `employees`, `attendances`; không tạo bảng `reports`.
- Migration: schema có unique department code, employee code, user profile và attendance theo ngày.
- Eloquent: `User hasOne Employee`; `Employee belongsTo User/Department` và `hasMany Attendances`; `Department hasMany Employees`; `Attendance belongsTo Employee`.
- Factory: `UserFactory`, `DepartmentFactory`, `EmployeeFactory`, `AttendanceFactory`.
- Seeder demo deterministic: 1 admin, 2 HR, 12 employee, 4 department, 540 attendance trong 45 ngày gần đây; có đủ present/late/absent/leave.
- Tài khoản demo: `admin@example.com`, `hr@example.com`, `employee1@example.com`; mật khẩu chung `Password123!` chỉ dùng cho dev/demo và được hash.
- Không hard-delete department có employee, employee có attendance hoặc user gắn employee nhờ `restrictOnDelete()`.
