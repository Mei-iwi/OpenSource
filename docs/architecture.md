# Kiến trúc

Đây là ứng dụng Laravel MVC thông thường, phù hợp với đồ án sinh viên; không sử dụng microservice, DDD hay Clean Architecture.

```text
Browser -> Routes -> Middleware (auth, account.active, role)
        -> Controller -> Form Request / Policy or Gate
        -> Eloquent Model -> MySQL -> Blade Response
```

Chức năng kiểm tra mã nhân viên dùng JavaScript/Fetch trong Blade, endpoint JSON, validation/query và MySQL. Layout/partial Blade dùng chung cho phần trình bày. Quan hệ Eloquent kết nối User, Employee, Department, Attendance và LeaveRequest; LeaveRequest độc lập với Attendance.
