# Automatic absent attendance

The Laravel scheduler runs `attendance:mark-absent` at 00:10 in `attendance.timezone` (configured by `ATTENDANCE_TIMEZONE`, falling back to `app.timezone`). The command processes the previous local calendar day, after that day has ended. Deployment must invoke `php artisan schedule:run` every minute using the existing Laravel scheduler integration.

For a past date, the command reads `attendance.work_days` and skips dates outside that workweek. It processes active employees hired by that date. If an attendance row already exists, the command leaves it unchanged. Otherwise, it creates `leave` for an approved leave request covering the date or `absent` when no such request exists. The existing unique `(employee_id, work_date)` constraint and `insertOrIgnore` make retries and concurrent runs idempotent. The leave approval sync remains responsible for converting an older `absent` row to `leave` when leave is approved later.

Manual retry/backfill: `php artisan attendance:mark-absent --date=YYYY-MM-DD`. Today and future dates are rejected in the configured timezone. The command logs the date, timezone and result counts without employee personal details.

The project has no holiday calendar or holiday table. This command therefore applies the configured workweek only. Holiday exclusions require a maintained holiday data source before they can be added reliably.
