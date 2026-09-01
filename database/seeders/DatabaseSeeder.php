<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = 'Password123!';

        $departments = collect([
            ['code' => 'PB-HC', 'name' => 'Hành chính - Nhân sự', 'description' => 'Quản lý con người và vận hành nội bộ.'],
            ['code' => 'PB-CNTT', 'name' => 'Công nghệ thông tin', 'description' => 'Phát triển và vận hành sản phẩm công nghệ.'],
            ['code' => 'PB-KD', 'name' => 'Kinh doanh', 'description' => 'Phụ trách khách hàng và tăng trưởng doanh thu.'],
            ['code' => 'PB-TC', 'name' => 'Tài chính - Kế toán', 'description' => 'Quản lý tài chính và kế toán doanh nghiệp.'],
        ])->map(fn (array $data) => Department::create($data));

        $admin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => $password,
            'role' => 'admin',
        ]);

        $hrUsers = User::factory(2)->sequence(
            ['name' => 'HR Manager', 'email' => 'hr@example.com'],
            ['name' => 'HR Assistant', 'email' => 'hr2@example.com'],
        )->create(['password' => $password, 'role' => 'hr']);

        $admin->employee()->create(['department_id' => $departments[0]->id, 'employee_code' => 'ADM-0001', 'position' => 'Quản trị viên', 'hire_date' => now()->subYears(3)->toDateString(), 'employment_status' => 'active']);
        $hrUsers->values()->each(function (User $user, int $index) use ($departments): void {
            $user->employee()->create(['department_id' => $departments[0]->id, 'employee_code' => sprintf('HR-%04d', $index + 1), 'position' => 'Nhân sự', 'hire_date' => now()->subYears(2)->subMonths($index)->toDateString(), 'employment_status' => 'active']);
        });

        $statuses = ['present', 'late', 'present', 'absent', 'leave'];

        foreach (range(1, 12) as $index) {
            $user = User::factory()->create([
                'name' => 'Nhân viên Demo '.$index,
                'email' => 'employee'.$index.'@example.com',
                'password' => $password,
                'role' => 'employee',
            ]);

            $employee = $user->employee()->create([
                'department_id' => $departments[($index - 1) % $departments->count()]->id,
                'employee_code' => sprintf('EMP-%04d', $index),
                'phone' => sprintf('090000%04d', $index),
                'address' => 'Hà Nội',
                'date_of_birth' => now()->subYears(25 + ($index % 10))->toDateString(),
                'position' => $index % 3 === 0 ? 'Trưởng nhóm' : 'Chuyên viên',
                'hire_date' => now()->subYears(1)->subDays($index)->toDateString(),
                'employment_status' => $index === 12 ? 'inactive' : 'active',
            ]);

            foreach (range(0, 44) as $day) {
                $status = $statuses[($day + $index) % count($statuses)];

                $employee->attendances()->create([
                    'work_date' => now()->subDays($day)->toDateString(),
                    'check_in' => in_array($status, ['present', 'late'], true) ? ($status === 'late' ? '08:30:00' : '08:00:00') : null,
                    'check_out' => in_array($status, ['present', 'late'], true) ? '17:00:00' : null,
                    'status' => $status,
                    'note' => $status === 'late' ? 'Đến muộn' : null,
                ]);
            }
        }
    }
}
