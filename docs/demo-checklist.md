# Demo checklist

Use this flow for the final presentation. Demo credentials are development-only and are documented in `README.md`.

| Step | Account | Route | Action | Expected result | Evidence |
|---|---|---|---|---|---|
| 1 | Admin | `/login` | Log in as Admin | Admin dashboard opens | `02-admin-dashboard.png` |
| 2 | Admin | `/admin/dashboard` | Show KPI cards and module link | Summary is visible | `02-admin-dashboard.png` |
| 3 | Admin | `/admin/users` | Search/filter, create HR/Employee, lock/unlock | Account management works | `03-admin-users.png` |
| 4 | HR | `/hr/dashboard` | Log in as HR | HR dashboard opens | `04-hr-dashboard.png` |
| 5 | HR | `/hr/departments` | Create/update department | Department data is saved | `05-departments.png` |
| 6 | HR | `/hr/employees` | Search/filter and open employee | Employee list and details render | `06-employees.png` |
| 7 | HR | `/hr/attendances` | Create/update attendance | Status and time validation work | `08-attendance.png` |
| 8 | HR | `/hr/reports` | Apply month/department/status filters | Counts and table match filters | `10-report.png` |
| 9 | HR | `/hr/reports/export.csv` | Export filtered result | UTF-8 CSV downloads | `12-csv-result.png` |
| 10 | HR | `/hr/reports/print` | Open print view | Browser print view hides controls | `13-print-preview.png` |
| 11 | HR | `/hr/employees/create` | Enter an existing/new employee code | Fetch JSON feedback is shown | `15-ajax-code-check.png` |
| 12 | HR | `/hr/employees/create` | Upload valid avatar | Avatar is stored and displayed | `16-avatar-upload.png` |
| 13 | Employee | `/login` | Log in as Employee | Employee dashboard opens | `14-employee-dashboard.png` |
| 14 | Employee | `/employee/profile` | View and update permitted fields | Own profile is shown and editable safely | `15-employee-profile.png` |
| 15 | Employee | `/employee/attendances` | Filter attendance history | Only own history is visible | `08-attendance.png` |
| 16 | Employee | Admin/HR route | Open a management URL directly | Server returns 403 | `19-tests-pass.png` |

Before presenting, confirm the database is the development database `hr_management`, not the test database.
