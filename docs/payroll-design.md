# Basic payroll design

## Scope and existing data

The application has employees, one attendance row per employee and date, and approved leave requests. Attendance statuses are `present`, `late`, `absent`, and `leave`. The configured workweek is `config('attendance.work_days')` (currently Monday–Friday). There is no overtime ledger, termination date, tax rule, or social insurance calculation. This module does not infer any of those rules.

## Salary structure

- `employee_salaries` stores one current configuration per employee: monthly base salary, fixed monthly allowance, and fixed monthly deduction. Only HR/Admin may edit it. All amounts are nonnegative `DECIMAL(18,2)` values; the application converts decimal strings to integer cents for calculations and never uses floating point for money.
- A generated payslip snapshots base salary, allowance, and the deduction actually applied after the gross-pay cap. Changing salary configuration later does not change a finalized payslip.
- The MVP uses one configured currency (`PAYROLL_CURRENCY`, default `VND`) for all salary and payslip amounts. Currency conversion is outside scope.
- The fixed deduction is an entered business amount, not an automatic tax or insurance calculation. Applied deduction is capped at gross pay so net pay cannot be negative.

## Period, work days, and leave

- `payroll_periods.period_month` is the first date of a calendar month and is unique. Period boundaries are inclusive. Generation is available to HR/Admin for a month and includes active employees with a salary configuration whose hire date is on or before month end.
- Scheduled days are dates in the month on configured workdays from the later of month start and hire date. Weekend attendance is not paid as overtime in this MVP.
- `present` and `late` attendance count as worked days. An approved annual leave day counts as paid when it falls on a scheduled day and does not already have worked attendance. An approved unpaid leave day is counted separately and is unpaid unless actual worked attendance exists. The calculation clips leave ranges to the month, including leave crossing a month boundary, and counts each date once.
- Approved `sick` or `other` leave on a scheduled day stops generation for that employee because the project has no pay policy for those categories. HR must resolve the data or wait for an explicit policy. Pending/rejected/cancelled leave has no payroll effect.
- `absent` and missing attendance do not earn base pay. The payslip records scheduled, worked, paid leave, unpaid leave, and unpaid/missing days so HR can inspect the basis before finalization.

## Calculation and rounding

For a month with `S > 0` scheduled days and `P` distinct worked or paid annual leave days:

`base_pay = round_half_up(base_salary_cents × P ÷ S)`

`attendance_deduction = base_salary_cents − base_pay`

`gross = base_pay + fixed_allowance_cents`

`applied_fixed_deduction = min(configured_fixed_deduction_cents, gross)`

`net = gross − applied_fixed_deduction`

With zero scheduled days, base pay is zero. Amounts are stored as decimal strings with two digits after the decimal point. The period and payslip persist all inputs and results as a snapshot.

## Status, locking, and authorization

- A period starts as `draft`. HR/Admin can regenerate a draft in a database transaction. The service locks the period row and replaces its draft payslips. A unique `(payroll_period_id, employee_id)` key prevents duplicate payslips.
- Finalization locks the period and changes it to `finalized` in one transaction. A finalized period cannot be regenerated or deleted through the application. Its payslips are read only. Corrections would require a separate adjustment/reopening workflow, which is outside this MVP.
- HR/Admin see periods and payslips and configure salaries. An employee can only open their own finalized payslip. The employee route resolves the payslip through their own employee relationship; direct IDs belonging to others return 404. Draft payslips are not visible to employees.
- Payroll and salary amounts are never added to public chat profile cards or generic employee responses.

## MVP screens

- HR/Admin: monthly period list, generate and finalize actions, period detail with totals, salary configuration form, and payslip detail.
- Employee: list of own finalized payslips and a printable payslip detail.

## Deferred decisions

Overtime, holidays, weekend work, taxes, statutory deductions, sick/other leave pay, adjustments, partial allowance proration, inactive employees, and reopening finalized payroll require explicit business rules before implementation.
