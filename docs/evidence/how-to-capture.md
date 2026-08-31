# How to capture evidence

1. Start the app with `php artisan serve` and `npm run dev`, then log in with a seeded Admin, HR or Employee account documented in the README.
2. Open the real routes: `/admin/dashboard`, `/admin/users`, `/hr/dashboard`, `/hr/departments`, `/hr/employees`, `/hr/attendances`, `/hr/reports`, `/employee/dashboard`, `/employee/profile` or `/employee/attendances`.
3. Capture the heading, relevant data/filter/action and role context. For authorization, capture the expected 403 page or use the automated test result.
4. For CSV, open `/hr/reports/export.csv` after applying a filter. For print, open `/hr/reports/print` and show browser print preview.
5. Use approximately 375px, 768px and 1280px viewport widths for responsive evidence.
6. Save files under `docs/evidence/` with the names in `screenshot-checklist.md`. Do not commit credentials or personal data.
