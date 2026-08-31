# UI rubric evidence

| Rubric criterion | Implementation | Page/route | Evidence filename | Status |
|---|---|---|---|---|
| Layout | Shared Blade app layout, navbar, sidebar and flash area | All authenticated routes | `01-layout.png` | PENDING MANUAL SCREENSHOT |
| Responsive | Tailwind responsive grids, horizontal table scrolling and mobile menu | Dashboards, tables, forms | `17-mobile-layout.png` | PENDING MANUAL SCREENSHOT |
| Role navigation | Role-aware navigation with active route styling | Admin/HR/Employee layouts | `02-admin-dashboard.png`, `04-hr-dashboard.png`, `14-employee-dashboard.png` | PENDING MANUAL SCREENSHOT |
| Dashboard | Consistent KPI cards and empty-safe summaries | Role dashboards | `02-admin-dashboard.png`, `04-hr-dashboard.png`, `14-employee-dashboard.png` | PENDING MANUAL SCREENSHOT |
| CRUD UI | Shared headers, actions, forms and empty states | Users, Departments, Employees | `03-admin-users.png`, `05-departments.png`, `06-employees.png` | PENDING MANUAL SCREENSHOT |
| Validation | Inline errors and consistent input styling | CRUD and attendance forms | `07-employee-form-validation.png` | PENDING MANUAL SCREENSHOT |
| Search/filter | Labeled controls, reset links and preserved query strings | Employees, Attendance, Reports | `09-attendance-filter.png`, `11-report-filter.png` | PENDING MANUAL SCREENSHOT |
| Pagination | Laravel pagination below responsive tables | Employees, Attendance, Reports | `06-employees.png` | PENDING MANUAL SCREENSHOT |
| Attendance | Status badges, readable tables and empty states | HR/Employee attendance | `08-attendance.png` | PENDING MANUAL SCREENSHOT |
| Reports | Summary cards, filter panel and report table | `/hr/reports` | `10-report.png`, `11-report-filter.png` | PENDING MANUAL SCREENSHOT |
| CSV | Filtered UTF-8 export action | `/hr/reports/export.csv` | `12-csv-result.png` | AUTOMATED TEST PASS; PENDING SCREENSHOT |
| Print | Dedicated view and print media rules hide navigation/actions | `/hr/reports/print` | `13-print-preview.png` | AUTOMATED TEST PASS; PENDING SCREENSHOT |
| AJAX | Employee-code availability feedback | Employee create/edit | `15-ajax-code-check.png` | AUTOMATED TEST PASS; PENDING SCREENSHOT |
| Avatar | Placeholder/image sizing and upload UI | Employee forms/profile | `16-avatar-upload.png` | AUTOMATED TEST PASS; PENDING SCREENSHOT |
| Authorization | Role middleware and ownership checks | Protected routes | `19-tests-pass.png` | AUTOMATED TEST PASS; PENDING SCREENSHOT |

No screenshot is fabricated by this document. Capture the named files during manual verification.
