# UI rubric evidence

| Rubric criterion | Implementation | Page/route | Evidence filename | Status |
|---|---|---|---|---|
| Layout | Shared Blade app layout, navbar, sidebar, flash area | All authenticated routes | `01-layout.png` | PASS — PENDING MANUAL SCREENSHOT |
| Responsive | Tailwind responsive grids, horizontal table scrolling, mobile menu | All dashboards and tables | `17-mobile-layout.png` | PASS — PENDING MANUAL SCREENSHOT |
| Role navigation | Server-rendered role-aware navigation with active route styling | Admin/HR/Employee layouts | `02-admin-dashboard.png`, `04-hr-dashboard.png`, `14-employee-dashboard.png` | PASS — PENDING MANUAL SCREENSHOT |
| Dashboard | Consistent KPI cards and empty-safe summary sections | `/admin/dashboard`, `/hr/dashboard`, `/employee/dashboard` | `02-admin-dashboard.png`, `04-hr-dashboard.png`, `14-employee-dashboard.png` | PASS — PENDING MANUAL SCREENSHOT |
| CRUD UI | Shared page headers, forms, action links and empty states | Users, Departments, Employees | `03-admin-users.png`, `05-departments.png`, `06-employees.png` | PASS — PENDING MANUAL SCREENSHOT |
| Validation | Inline errors and consistent input styling | User/Department/Employee/Attendance forms | `07-employee-form-validation.png` | PASS — PENDING MANUAL SCREENSHOT |
| Search/filter | Labeled filter forms with reset links and preserved query strings | Employees, Attendance, Reports | `09-attendance-filter.png`, `11-report-filter.png` | PASS — PENDING MANUAL SCREENSHOT |
| Pagination | Laravel pagination rendered below responsive tables | Employees, Attendance, Reports | `06-employees.png` | PASS — PENDING MANUAL SCREENSHOT |
| Attendance | Status badges, empty state and readable table | `/hr/attendances`, `/employee/attendances` | `08-attendance.png` | PASS — PENDING MANUAL SCREENSHOT |
| Reports | Summary cards, filter panel and report table | `/hr/reports` | `10-report.png`, `11-report-filter.png` | PASS — PENDING MANUAL SCREENSHOT |
| CSV | Explicit export action for filtered data | `/hr/reports/export.csv` | `12-csv-result.png` | PASS — automated test |
| Print | Dedicated print view and print media rules hide navigation/actions | `/hr/reports/print` | `13-print-preview.png` | PASS — PENDING MANUAL SCREENSHOT |
| AJAX | Employee-code availability feedback in employee form | Employee create/edit | `15-ajax-code-check.png` | PASS — automated test |
| Avatar | Placeholder/image sizing and upload UI | Employee create/edit/profile | `16-avatar-upload.png` | PASS — PENDING MANUAL SCREENSHOT |
| Authorization | Role-aware UI plus server-side middleware/policy | All protected routes | `19-tests-pass.png` | PASS — automated test |

No screenshot is fabricated by this document. Capture the named files during manual verification and store them under `docs/evidence/`.
