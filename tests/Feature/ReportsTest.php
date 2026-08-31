<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    private function hr(): User { return User::factory()->create(['role' => 'hr']); }

    public function test_dashboards_show_aggregated_kpis(): void
    {
        $department = Department::factory()->create(['name' => 'Engineering']);
        $active = Employee::factory()->create(['department_id' => $department->id, 'employment_status' => 'active']);
        $inactive = Employee::factory()->create(['employment_status' => 'inactive']);
        Attendance::factory()->create(['employee_id' => $active->id, 'work_date' => '2026-08-01', 'status' => 'present']);
        Attendance::factory()->create(['employee_id' => $active->id, 'work_date' => '2026-08-02', 'status' => 'late']);
        $this->actingAs($this->hr())->get('/hr/dashboard')->assertOk()->assertSee('Engineering')->assertSee('2')->assertSee('Có mặt');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/dashboard')->assertOk()->assertSee('Tài khoản theo role')->assertSee('Module HR');
        $this->assertNotSame($active->id, $inactive->id);
    }

    public function test_report_filters_and_aggregates_statuses(): void
    {
        $department = Department::factory()->create();
        $employee = Employee::factory()->create(['department_id' => $department->id]);
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-10', 'status' => 'present']);
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-11', 'status' => 'late']);
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-07-10', 'status' => 'absent']);
        $response = $this->actingAs($this->hr())->get('/hr/reports?month=8&year=2026&department_id='.$department->id.'&status=late');
        $response->assertOk()->assertSee('1')->assertSee('Đi muộn');
        $response->assertSee('month=8', false)->assertDontSee('10/07/2026');
    }

    public function test_report_csv_is_filtered_utf8_and_authorized(): void
    {
        $employee = Employee::factory()->create();
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-08-12', 'status' => 'leave']);
        Attendance::factory()->create(['employee_id' => $employee->id, 'work_date' => '2026-07-12', 'status' => 'present']);
        $response = $this->actingAs($this->hr())->get('/hr/reports/export.csv?month=8&year=2026');
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8')->assertHeader('content-disposition');
        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent());
        $this->assertStringContainsString('12/08/2026', $response->streamedContent());
        $this->assertStringNotContainsString('12/07/2026', $response->streamedContent());
        $this->actingAs(User::factory()->create(['role' => 'employee']))->get('/hr/reports')->assertForbidden();
    }

    public function test_admin_and_hr_can_open_print_report(): void
    {
        Attendance::factory()->create();
        $this->actingAs($this->hr())->get('/hr/reports/print')->assertOk()->assertSee('window.print()');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/hr/reports')->assertOk();
    }
}
