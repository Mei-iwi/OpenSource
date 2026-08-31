# ERD — Website Quản lý Nhân sự

```mermaid
erDiagram
    USERS ||--o| EMPLOYEES : has_profile
    DEPARTMENTS ||--o{ EMPLOYEES : contains
    EMPLOYEES ||--o{ ATTENDANCES : records

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string role
        string account_status
    }
    DEPARTMENTS {
        bigint id PK
        string code UK
        string name
        text description
    }
    EMPLOYEES {
        bigint id PK
        bigint user_id FK_UK
        bigint department_id FK
        string employee_code UK
        string phone
        text address
        date date_of_birth
        string position
        date hire_date
        string employment_status
        string avatar_path
    }
    ATTENDANCES {
        bigint id PK
        bigint employee_id FK
        date work_date
        time check_in
        time check_out
        string status
        text note
    }
```

`employees.user_id` là unique; `attendances` có unique kép `(employee_id, work_date)`. Foreign key tới department/employee/user dùng restrict khi xóa để giữ toàn vẹn dữ liệu và lịch sử.
