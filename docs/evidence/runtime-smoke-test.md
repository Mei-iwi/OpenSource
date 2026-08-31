# Runtime smoke test

The preview routes were removed because the real role dashboards now receive their data from controllers. The smoke test uses Laravel HTTP requests on the MySQL test database and covers populated GET pages plus removed preview URLs.

| URL | role | expected status | actual status | result |
|---|---|---:|---:|---|
| `/` | public | 200 | 200 | PASS |
| `/login` | public | 200 | 200 | PASS |
| `/admin/dashboard`, `/admin/users` | admin | 200 | 200 | PASS |
| `/hr/dashboard`, `/hr/departments`, `/hr/employees` | hr | 200 | 200 | PASS |
| `/hr/attendances`, `/hr/reports` | hr | 200 | 200 | PASS |
| `/hr/reports/export.csv`, `/hr/reports/print` | hr | 200 | 200 | PASS |
| `/employee/dashboard`, `/employee/profile`, `/employee/attendances` | employee | 200 | 200 | PASS |
| `/preview/admin`, `/preview/hr`, `/preview/employee` | public | 404 | 404 | PASS |
