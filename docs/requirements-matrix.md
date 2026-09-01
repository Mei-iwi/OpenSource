# Ma trận yêu cầu

| ID | Requirement | Role | Route/Module | Implementation | Test | Evidence | Status |
|---|---|---|---|---|---|---|---|
| R01 | Authentication and logout | All | `/login`, `/logout` | Breeze session auth | AuthenticationTest | manual cases | PASS |
| R02 | Role dashboards and authorization | All | role dashboards | Role middleware | RoleAuthorizationTest | ui-rubric | PASS |
| R03 | User/role/account management | Admin | `/admin/users` | Resource controller and requests | AdminUserManagementTest | manual cases | PASS |
| R04 | Department CRUD and safe deletion | Admin/HR | `/hr/departments` | Resource controller, relationship check | HrCrudTest | ui-rubric | PASS |
| R05 | Employee CRUD and filters | Admin/HR | `/hr/employees` | Transaction, eager loading, requests | HrCrudTest | ui-rubric | PASS |
| R06 | Attendance management and uniqueness | Admin/HR | `/hr/attendances` | Validation and database unique key | AttendanceSelfServiceTest, DatabaseSchemaTest | manual cases | PASS |
| R07 | Employee self-service ownership | Employee | `/employee/profile`, `/employee/attendances` | Gate/policy ownership and safe fields | AttendanceSelfServiceTest, ProfileTest | ui-rubric | PASS |
| R08 | Dashboard/report aggregates | Admin/HR | `/hr/reports` | COUNT/GROUP BY and filtered query | ReportsTest | reports.md | PASS |
| R09 | CSV and browser print | Admin/HR | report export/print | UTF-8 stream and print CSS | ReportsTest | screenshot checklist | PASS |
| R10 | AJAX employee-code check | Admin/HR | `/hr/employees/check-code` | Fetch JSON endpoint | SecurityPolishTest | ui-rubric | PASS |
| R11 | Avatar upload | Admin/HR/Employee | employee forms/profile | Public Storage and MIME/size validation | SecurityPolishTest | screenshot checklist | PASS |
| R12 | CI and test evidence | Maintainers | GitHub Actions/docs | PHP 8.3, MySQL 8.4 workflow | Full suite | evidence index | PASS |
| R13 | Leave Request workflow | Employee/Admin/HR | `/employee/leave-requests`, `/hr/leave-requests` | Form Request, ownership, overlap and review status | LeaveRequestTest | leave flow diagram | PASS |
