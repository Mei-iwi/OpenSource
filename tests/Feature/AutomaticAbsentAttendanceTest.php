<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AutomaticAbsentAttendanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('attendance.timezone', 'Asia/Ho_Chi_Minh');
        config()->set('attendance.work_days', [1, 2, 3, 4, 5]);
        DB::purge('sqlite');

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->date('hire_date');
            $table->string('employment_status');
            $table->timestamps();
        });
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('work_date');
            $table->string('status');
            $table->string('note')->nullable();
            $table->unique(['employee_id', 'work_date']);
            $table->timestamps();
        });
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
        });

        Carbon::setTestNow(Carbon::parse('2026-11-10 01:00:00', 'Asia/Ho_Chi_Minh'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function employee(string $status = 'active', string $hireDate = '2026-01-01'): int
    {
        return DB::table('employees')->insertGetId([
            'hire_date' => $hireDate,
            'employment_status' => $status,
        ]);
    }

    public function test_missing_attendance_becomes_absent_and_rerun_is_idempotent(): void
    {
        $employeeId = $this->employee();

        $this->artisan('attendance:mark-absent')->assertSuccessful();
        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employeeId,
            'work_date' => '2026-11-09',
            'status' => 'absent',
        ]);

        $this->artisan('attendance:mark-absent')->assertSuccessful();
        $this->assertSame(1, DB::table('attendances')->where('employee_id', $employeeId)->count());
    }

    public function test_existing_attendance_is_preserved(): void
    {
        $employeeId = $this->employee();
        DB::table('attendances')->insert([
            'employee_id' => $employeeId,
            'work_date' => '2026-11-09',
            'status' => 'late',
            'note' => 'Existing check-in',
        ]);

        $this->artisan('attendance:mark-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', [
            'employee_id' => $employeeId,
            'work_date' => '2026-11-09',
            'status' => 'late',
            'note' => 'Existing check-in',
        ]);
        $this->assertSame(1, DB::table('attendances')->count());
    }

    public function test_approved_leave_becomes_leave_while_other_statuses_become_absent(): void
    {
        $approvedId = $this->employee();
        $pendingId = $this->employee();
        foreach ([$approvedId => 'approved', $pendingId => 'pending'] as $employeeId => $status) {
            DB::table('leave_requests')->insert([
                'employee_id' => $employeeId,
                'start_date' => '2026-11-08',
                'end_date' => '2026-11-10',
                'status' => $status,
            ]);
        }

        $this->artisan('attendance:mark-absent')->assertSuccessful();

        $this->assertDatabaseHas('attendances', ['employee_id' => $approvedId, 'status' => 'leave']);
        $this->assertDatabaseHas('attendances', ['employee_id' => $pendingId, 'status' => 'absent']);
    }

    public function test_inactive_and_not_yet_hired_employees_are_ignored(): void
    {
        $this->employee('inactive');
        $this->employee('active', '2026-11-10');

        $this->artisan('attendance:mark-absent')->assertSuccessful();

        $this->assertSame(0, DB::table('attendances')->count());
    }

    public function test_non_workday_is_skipped_and_configured_workday_is_processed(): void
    {
        $employeeId = $this->employee();

        $this->artisan('attendance:mark-absent', ['--date' => '2026-11-08'])->assertSuccessful();
        $this->assertSame(0, DB::table('attendances')->count());

        config()->set('attendance.work_days', [7]);
        $this->artisan('attendance:mark-absent', ['--date' => '2026-11-08'])->assertSuccessful();
        $this->assertDatabaseHas('attendances', ['employee_id' => $employeeId, 'work_date' => '2026-11-08', 'status' => 'absent']);
    }

    public function test_default_date_uses_local_timezone_and_never_processes_current_day(): void
    {
        $employeeId = $this->employee();
        Carbon::setTestNow(Carbon::parse('2026-11-09 17:15:00', 'UTC'));

        $this->artisan('attendance:mark-absent')->assertSuccessful();
        $this->assertDatabaseHas('attendances', ['employee_id' => $employeeId, 'work_date' => '2026-11-09']);

        $this->artisan('attendance:mark-absent', ['--date' => '2026-11-10'])->assertFailed();
        $this->artisan('attendance:mark-absent', ['--date' => '2026-02-30'])->assertFailed();
        $this->assertSame(1, DB::table('attendances')->count());
    }
}
