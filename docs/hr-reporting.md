# HR reporting data notes

- **Employee movement:** new hires use `employees.hire_date`. Inactive/resigned records use the nullable `employment_end_date`. New transitions to inactive receive the entered end date or the current date; reactivation clears it. Older inactive records without an end date appear only when no period is selected. Department is the employee's **current** department because the project has no department history.
- **Leave summary:** a leave request is included when its date range overlaps the requested range. Status and leave type come from the request record. The report does not infer paid days or payroll treatment.
- **Late attendance:** counts attendance rows with status `late` in the selected month/year. Early departure is unavailable because the project has check-out times but no standard shift end time or early-departure rule.
- **Scale:** report pages use database filters and 20-row pagination. CSV exports stream results in 500-row chunks. The existing browser Print attendance view is capped at 1,000 rows and indicates truncation; CSV covers the full filtered result.
- **Access and CSV:** all report and export routes require HR/Admin. CSV values use the same spreadsheet formula escape rule across attendance and the new reports. Legal identifiers and salary data are not included.
- **PDF:** the project has no server-side PDF package or requirement. Browser Print remains the print path; no DomPDF dependency was added.
