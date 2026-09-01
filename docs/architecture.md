# Kiến trúc

Đây là ứng dụng Laravel MVC thông thường, phù hợp với đồ án sinh viên; không sử dụng microservice, DDD hay Clean Architecture.

```text
Browser -> Routes -> Middleware (auth, account.active, role)
        -> Controller -> Form Request / Policy or Gate
        -> Eloquent Model -> MySQL -> Blade Response
```

Chức năng kiểm tra mã nhân viên dùng JavaScript/Fetch trong Blade, endpoint JSON, validation/query và MySQL. Layout/partial Blade dùng chung cho phần trình bày. Quan hệ Eloquent kết nối User, Employee, Department, Attendance và LeaveRequest; LeaveRequest độc lập với Attendance.
## ATT-05 regression additions

Self attendance resolves `auth()->user()->employee` server-side and writes attendance proof paths to the private local filesystem. The proof controller streams only authorized files: Admin/HR use attendance-management permission and Employee ownership is checked against the attendance employee. No public attendance-proof URL, biometric processing, or face recognition is used.

The application shell reads navigation preferences before render and supports left/right vertical navigation plus top/bottom horizontal navigation. State and position remain browser-local and are not persisted in the database.
