# Kiểm tra nhanh runtime

The smoke test uses Laravel HTTP requests on MySQL `hr_management_testing` and covers the real populated pages used by the application.

| URL | Role | Expected status | Actual status | Result |
|---|---|---:|---:|---|
| `/` | public | 200 | 200 | PASS |
| `/login` | public | 200 | 200 | PASS |
| `/admin/dashboard`, `/admin/users` | admin | 200 | 200 | PASS |
| `/hr/dashboard`, `/hr/departments`, `/hr/employees` | hr | 200 | 200 | PASS |
| `/hr/attendances`, `/hr/reports` | hr | 200 | 200 | PASS |
| `/hr/reports/export.csv`, `/hr/reports/print` | hr | 200 | 200 | PASS |
| `/employee/dashboard`, `/employee/profile`, `/employee/attendances` | employee | 200 | 200 | PASS |
