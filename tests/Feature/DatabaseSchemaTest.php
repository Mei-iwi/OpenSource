<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_eloquent_relations_work(): void
    {
        $department = Department::factory()->create();
        $user = User::factory()->create(['role' => 'employee']);
        $employee = Employee::factory()->create([
            'user_id' => $user->id,
            'department_id' => $department->id,
        ]);
        $attendance = Attendance::factory()->create([
            'employee_id' => $employee->id,
            'work_date' => '2026-08-31',
        ]);

        $this->assertTrue($user->employee->is($employee));
        $this->assertTrue($employee->department->is($department));
        $this->assertTrue($employee->attendances->first()->is($attendance));
        $this->assertTrue($department->employees->first()->is($employee));
    }

    public function test_attendance_date_is_unique_per_employee(): void
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-31']);

        $this->expectException(QueryException::class);
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-31']);
    }
}
