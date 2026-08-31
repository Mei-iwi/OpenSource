# Ma trận kiểm thử tự động

| Area | Test files | Coverage |
|---|---|---|
| Authentication | `tests/Feature/Auth/*`, `AuthenticationTest.php` | Login, logout, password and verification |
| Authorization | `RoleAuthorizationTest.php`, `AdminUserManagementTest.php` | Guest, role dashboards, locked users and admin-only actions |
| Department/Employee | `HrCrudTest.php` | CRUD, validation, transaction, search/filter/pagination |
| Attendance/Self-Service | `AttendanceSelfServiceTest.php`, `DatabaseSchemaTest.php` | CRUD validation, unique day, ownership and protected fields |
| Reports | `ReportsTest.php` | Aggregates, filters, CSV, print and role access |
| AJAX/Avatar | `SecurityPolishTest.php` | JSON availability, MIME/size, storage and ownership |
| Runtime | `RuntimeSmokeTest.php` | Main populated pages render without HTTP 500 |

Latest verified run: MySQL `hr_management_testing`, 61 tests, 222 assertions, 0 failures.
