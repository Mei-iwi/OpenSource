# Reports and statistics evidence

## Scope

Admin and HR access `/hr/reports`, `/hr/reports/export.csv` and `/hr/reports/print`. Employee requests are rejected by the existing `role:admin,hr` middleware.

## Query techniques

- `COUNT(*)` and `GROUP BY status` aggregate attendance states.
- `COUNT(*)` and `GROUP BY department` aggregate employees per department.
- Eloquent `with(['employee.user', 'employee.department'])` prevents detail-table N+1 queries.
- The report filter query is reused by HTML, CSV and print endpoints.

## Filters and output

Reports support month, year, department, employee, attendance status and employment status. Pagination preserves query strings. CSV uses `streamDownload`, a UTF-8 BOM and Vietnamese headers. Print uses Blade/CSS `@media print` and browser Print/Save as PDF.

## Screenshot checklist

- [ ] HR dashboard KPI cards and department/status summaries.
- [ ] Filtered report table with summary cards.
- [ ] CSV opened in Excel with Vietnamese text.
- [ ] Print preview hides navigation and action controls.
