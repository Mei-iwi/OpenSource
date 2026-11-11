<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Support\CsvCell;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class HrReportingTest extends TestCase
{
    private User $hr;

    private int $sequence = 1;

    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role');
            $table->string('account_status');
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('department_id');
            $table->string('employee_code')->unique();
            $table->date('hire_date');
            $table->string('position')->nullable();
            $table->string('employment_status');
            $table->string('avatar_path')->nullable();
            $table->timestamps();
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('work_date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->string('leave_type');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('reason');
            $table->string('status');
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->boolean('is_active');
            $table->timestamps();
        });
        (require database_path('migrations/2026_11_09_000000_add_employment_end_date_to_employees_table.php'))->up();
        DB::table('departments')->insert([
            ['id' => 1, 'code' => 'ENG', 'name' => 'Engineering'],
            ['id' => 2, 'code' => 'HR', 'name' => 'People'],
        ]);
        DB::table('job_positions')->insert(['id' => 1, 'name' => 'Staff']);
        $this->hr = $this->user('hr', 'hr@example.com', 'HR Manager');
    }

    private function user(string $role, string $email, string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'Password123!',
            'role' => $role,
            'account_status' => 'active',
        ]);
    }

    private function employee(string $name, string $hireDate = '2026-10-01', string $status = 'active', ?string $endDate = null, int $department = 1): Employee
    {
        $id = $this->sequence++;
        $user = $this->user('employee', "person{$id}@example.com", $name);

        return Employee::create([
            'user_id' => $user->id,
            'department_id' => $department,
            'employee_code' => "EMP-{$id}",
            'hire_date' => $hireDate,
            'position' => 'Staff',
            'employment_status' => $status,
            'employment_end_date' => $endDate,
        ]);
    }

    private function leave(Employee $employee, string $type, string $status, string $start, string $end): LeaveRequest
    {
        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $type,
            'status' => $status,
            'start_date' => $start,
            'end_date' => $end,
            'reason' => 'Test leave',
        ]);
    }

    public function test_movement_filters_month_year_type_and_handles_undated_old_records(): void
    {
        $this->employee('New Hire', '2026-10-02');
        $this->employee('Departed', '2024-01-01', 'inactive', '2026-10-15');
        $this->employee('Undated', '2024-01-01', 'inactive');

        $response = $this->actingAs($this->hr)->get(route('hr.reports.movement', ['month' => 10, 'year' => 2026]));
        $response->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 2);
        $response->assertSee('New Hire')->assertSee('Departed')->assertDontSee('Undated');

        $this->actingAs($this->hr)->get(route('hr.reports.movement', ['month' => 10, 'year' => 2026, 'movement_type' => 'inactive']))
            ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1)->assertSee('Departed')->assertDontSee('New Hire');
        $this->actingAs($this->hr)->get(route('hr.reports.movement', ['year' => 2025]))
            ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 0)->assertSee('Không có dữ liệu phù hợp');
    }

    public function test_inactive_transition_records_date_and_reactivation_clears_it(): void
    {
        $employee = $this->employee('Worker', '2024-01-01');
        $payload = [
            'name' => 'Worker', 'email' => $employee->user->email,
            'department_id' => 1, 'position' => 'Staff', 'hire_date' => '2024-01-01',
            'employment_status' => 'inactive',
        ];
        $this->actingAs($this->hr)->put(route('hr.employees.update', $employee), $payload)
            ->assertRedirect(route('hr.employees.index'));
        $businessToday = Carbon::today(config('attendance.timezone'));
        $this->assertSame($businessToday->toDateString(), $employee->fresh()->employment_end_date->toDateString());

        $this->actingAs($this->hr)->get(route('hr.reports.movement', [
            'movement_type' => 'inactive', 'month' => $businessToday->month, 'year' => $businessToday->year,
        ]))->assertViewHas('rows', fn ($rows) => $rows->total() === 1);

        $this->actingAs($this->hr)->put(route('hr.employees.update', $employee), [...$payload, 'employment_status' => 'active'])
            ->assertRedirect(route('hr.employees.index'));
        $this->assertNull($employee->fresh()->employment_end_date);
    }

    public function test_leave_filters_overlap_department_type_and_status(): void
    {
        $first = $this->employee('First');
        $second = $this->employee('Second', department: 2);
        $this->leave($first, 'annual', 'approved', '2026-09-29', '2026-10-02');
        $this->leave($first, 'unpaid', 'pending', '2026-10-10', '2026-10-11');
        $this->leave($second, 'annual', 'approved', '2026-10-01', '2026-10-03');

        $response = $this->actingAs($this->hr)->get(route('hr.reports.leave', [
            'start_date' => '2026-10-01', 'end_date' => '2026-10-31',
            'department_code' => 'ENG', 'leave_type' => 'annual', 'status' => 'approved',
        ]));
        $response->assertOk()->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $response->assertSee('First')->assertDontSee('Second');
        $this->actingAs($this->hr)->get(route('hr.reports.leave', ['start_date' => '2026-11-01', 'end_date' => '2026-10-01']))
            ->assertSessionHasErrors(['end_date']);
    }

    public function test_late_report_counts_only_requested_period_and_department(): void
    {
        $first = $this->employee('First');
        $second = $this->employee('Second', department: 2);
        $this->employee('Future', '2027-01-01');
        foreach (['2026-10-01', '2026-10-02', '2026-11-01'] as $date) {
            Attendance::create(['employee_id' => $first->id, 'work_date' => $date, 'status' => 'late']);
        }
        Attendance::create(['employee_id' => $second->id, 'work_date' => '2026-10-01', 'status' => 'late']);
        Attendance::create(['employee_id' => $first->id, 'work_date' => '2026-10-03', 'status' => 'present']);

        $response = $this->actingAs($this->hr)->get(route('hr.reports.late', ['month' => 10, 'year' => 2026, 'department_code' => 'ENG']));
        $response->assertOk()->assertViewHas('rows', function ($rows) {
            return $rows->total() === 1 && (int) $rows->first()->late_count === 2;
        });
        $response->assertSee('Chưa tính')->assertDontSee('Second');
    }

    public function test_reports_include_the_last_day_of_a_month(): void
    {
        $employee = $this->employee('Month End', '2026-10-31');
        $this->leave($employee, 'annual', 'approved', '2026-10-31', '2026-10-31');
        Attendance::create(['employee_id' => $employee->id, 'work_date' => '2026-10-31', 'status' => 'late']);

        $this->actingAs($this->hr)->get(route('hr.reports.movement', ['month' => 10, 'year' => 2026]))
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $this->actingAs($this->hr)->get(route('hr.reports.leave', ['start_date' => '2026-10-31', 'end_date' => '2026-10-31']))
            ->assertViewHas('rows', fn ($rows) => $rows->total() === 1);
        $this->actingAs($this->hr)->get(route('hr.reports.late', ['month' => 10, 'year' => 2026]))
            ->assertViewHas('rows', fn ($rows) => (int) $rows->first()->late_count === 1);
    }

    public function test_csv_exports_preserve_filters_and_escape_formula_cells(): void
    {
        $this->assertSame("'\u{200B}=1+1", CsvCell::safe("\u{200B}=1+1"));
        $injected = $this->employee('=HYPERLINK("https://example.test")');
        $other = $this->employee('Other', department: 2);
        $this->leave($injected, 'annual', 'approved', '2026-10-01', '2026-10-02');
        $this->leave($other, 'annual', 'approved', '2026-10-01', '2026-10-02');
        Attendance::create(['employee_id' => $injected->id, 'work_date' => '2026-10-01', 'status' => 'late', 'note' => '=1+1']);

        $movement = $this->actingAs($this->hr)->get(route('hr.reports.movement.csv', ['month' => 10, 'year' => 2026, 'department_code' => 'ENG']));
        $this->assertStringContainsString("'=HYPERLINK", $movement->streamedContent());
        $this->assertStringNotContainsString('Other', $movement->streamedContent());

        $leave = $this->actingAs($this->hr)->get(route('hr.reports.leave.csv', ['department_code' => 'ENG']));
        $this->assertStringContainsString("'=HYPERLINK", $leave->streamedContent());
        $this->assertStringNotContainsString('Other', $leave->streamedContent());

        $late = $this->actingAs($this->hr)->get(route('hr.reports.late.csv', ['month' => 10, 'year' => 2026, 'department_code' => 'ENG']));
        $this->assertStringContainsString("'=HYPERLINK", $late->streamedContent());
        $this->assertStringNotContainsString('Other', $late->streamedContent());

        $attendance = $this->actingAs($this->hr)->get(route('hr.reports.export'));
        $this->assertStringContainsString("'=1+1", $attendance->streamedContent());
    }

    public function test_authorization_blocks_employee_from_reports_and_csv(): void
    {
        $employee = $this->employee('Worker');
        foreach (['hr.reports.movement', 'hr.reports.leave', 'hr.reports.late', 'hr.reports.movement.csv', 'hr.reports.leave.csv', 'hr.reports.late.csv'] as $route) {
            $this->actingAs($employee->user)->get(route($route))->assertForbidden();
        }
    }

    public function test_large_result_is_paginated_and_print_remains_bounded_html(): void
    {
        for ($i = 0; $i < 45; $i++) {
            $this->employee("Worker {$i}");
        }
        $this->actingAs($this->hr)->get(route('hr.reports.movement', ['year' => 2026]))
            ->assertOk()->assertViewHas('rows', fn ($rows) => $rows->count() === 20 && $rows->total() === 45);

        $employee = Employee::firstOrFail();
        $date = Carbon::parse('2023-01-01');
        $rows = [];
        for ($i = 0; $i < 1001; $i++) {
            $rows[] = ['employee_id' => $employee->id, 'work_date' => $date->copy()->addDays($i)->toDateString(), 'status' => 'present'];
        }
        DB::table('attendances')->insert($rows);
        $this->actingAs($this->hr)->get(route('hr.reports.print'))
            ->assertOk()->assertViewHas('attendances', fn ($attendances) => $attendances->count() === 1000)
            ->assertViewHas('printTruncated', true)
            ->assertHeader('content-type', 'text/html; charset=UTF-8');
    }
}
