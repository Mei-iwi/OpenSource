<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
         * Mật khẩu dùng chung cho môi trường demo:
         * Password123!
         *
         * Nếu User model có:
         * 'password' => 'hashed'
         * thì Laravel sẽ tự hash password.
         */
        $password = 'Password123!';

        /*
        |--------------------------------------------------------------------------
        | 1. Phòng ban
        |--------------------------------------------------------------------------
        */

        $departments = collect([
            [
                'code' => 'PB-HCNS',
                'name' => 'Hành chính - Nhân sự',
                'description' => 'Quản lý nhân sự, tuyển dụng, hồ sơ lao động và công tác hành chính.',
            ],
            [
                'code' => 'PB-CNTT',
                'name' => 'Công nghệ thông tin',
                'description' => 'Phát triển, bảo trì hệ thống phần mềm và hạ tầng công nghệ.',
            ],
            [
                'code' => 'PB-KD',
                'name' => 'Kinh doanh',
                'description' => 'Phát triển khách hàng, tư vấn dịch vụ và quản lý doanh số.',
            ],
            [
                'code' => 'PB-TCKT',
                'name' => 'Tài chính - Kế toán',
                'description' => 'Quản lý tài chính, kế toán, chi phí và báo cáo doanh nghiệp.',
            ],
            [
                'code' => 'PB-CSKH',
                'name' => 'Chăm sóc khách hàng',
                'description' => 'Tiếp nhận yêu cầu, hỗ trợ và duy trì quan hệ với khách hàng.',
            ],
        ])->mapWithKeys(function (array $data) {
            $department = Department::updateOrCreate(
                ['code' => $data['code']],
                $data
            );

            return [$data['code'] => $department];
        });

        /*
        |--------------------------------------------------------------------------
        | 2. Quản trị viên
        |--------------------------------------------------------------------------
        */

        $adminAttributes = [
            'name' => 'Nguyễn Minh Quân',
            'email' => 'quan.nm@admin.hr-management.com',
            'password' => $password,
            'role' => 'admin',
            'account_status' => 'active',
            'email_verified_at' => now(),
        ];
        $admin = User::where('email', $adminAttributes['email'])
            ->orWhereHas('employee', fn ($query) => $query->where('employee_code', 'ADM-0001'))
            ->first();
        $admin ? $admin->update($adminAttributes) : $admin = User::create($adminAttributes);

        $admin->employee()->updateOrCreate([], [
            'department_id' => $departments['PB-CNTT']->id,
            'employee_code' => $admin->employee?->employee_code ?? 'ADM-0001',
            'phone' => '0901000001',
            'address' => 'Quận 7, TP. Hồ Chí Minh',
            'date_of_birth' => '1990-05-18',
            'position' => 'Quản trị hệ thống',
            'hire_date' => '2021-03-15',
            'employment_status' => 'active',
        ]);

        /*
        |--------------------------------------------------------------------------
        | 3. Nhân sự HR
        |--------------------------------------------------------------------------
        */

        $hrData = [
            [
                'name' => 'Trần Ngọc Anh',
                'email' => 'anh.tn@hr.hr-management.com',
                'employee_code' => 'HR-0001',
                'phone' => '0901000002',
                'address' => 'Quận Bình Thạnh, TP. Hồ Chí Minh',
                'date_of_birth' => '1992-08-12',
                'position' => 'Trưởng phòng Nhân sự',
                'hire_date' => '2021-08-02',
            ],
            [
                'name' => 'Lê Thị Thu Hà',
                'email' => 'ha.ltt@hr.hr-management.com',
                'employee_code' => 'HR-0002',
                'phone' => '0901000003',
                'address' => 'TP. Thủ Đức, TP. Hồ Chí Minh',
                'date_of_birth' => '1997-04-26',
                'position' => 'Chuyên viên Nhân sự',
                'hire_date' => '2023-01-09',
            ],
        ];

        foreach ($hrData as $item) {
            $userAttributes = [
                'name' => $item['name'],
                'email' => $item['email'],
                'password' => $password,
                'role' => 'hr',
                'account_status' => 'active',
                'email_verified_at' => now(),
            ];
            $user = User::where('email', $userAttributes['email'])
                ->orWhereHas('employee', fn ($query) => $query->where('employee_code', $item['employee_code']))
                ->first();
            $user ? $user->update($userAttributes) : $user = User::create($userAttributes);

            $user->employee()->updateOrCreate([], [
                'department_id' => $departments['PB-HCNS']->id,
                'employee_code' => $user->employee?->employee_code ?? $item['employee_code'],
                'phone' => $item['phone'],
                'address' => $item['address'],
                'date_of_birth' => $item['date_of_birth'],
                'position' => $item['position'],
                'hire_date' => $item['hire_date'],
                'employment_status' => 'active',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Danh sách nhân viên
        |--------------------------------------------------------------------------
        */

        $employees = [
            // Công nghệ thông tin
            [
                'name' => 'Phạm Quốc Huy',
                'email' => 'huy.pq@emp.hr-management.com',
                'department' => 'PB-CNTT',
                'code' => 'IT-0001',
                'phone' => '0902000001',
                'address' => 'Quận Gò Vấp, TP. Hồ Chí Minh',
                'dob' => '1995-02-14',
                'position' => 'Trưởng nhóm Phát triển',
                'hire_date' => '2022-04-11',
            ],
            [
                'name' => 'Nguyễn Hoàng Nam',
                'email' => 'nam.nh@emp.hr-management.com',
                'department' => 'PB-CNTT',
                'code' => 'IT-0002',
                'phone' => '0902000002',
                'address' => 'Quận Tân Bình, TP. Hồ Chí Minh',
                'dob' => '1999-07-03',
                'position' => 'Lập trình viên Backend',
                'hire_date' => '2024-01-08',
            ],
            [
                'name' => 'Võ Gia Bảo',
                'email' => 'bao.vg@emp.hr-management.com',
                'department' => 'PB-CNTT',
                'code' => 'IT-0003',
                'phone' => '0902000003',
                'address' => 'TP. Thủ Đức, TP. Hồ Chí Minh',
                'dob' => '2000-11-21',
                'position' => 'Lập trình viên Frontend',
                'hire_date' => '2024-03-18',
            ],
            [
                'name' => 'Đặng Minh Khoa',
                'email' => 'khoa.dm@emp.hr-management.com',
                'department' => 'PB-CNTT',
                'code' => 'IT-0004',
                'phone' => '0902000004',
                'address' => 'Quận 12, TP. Hồ Chí Minh',
                'dob' => '1998-09-09',
                'position' => 'Kỹ sư hệ thống',
                'hire_date' => '2023-06-05',
            ],

            // Kinh doanh
            [
                'name' => 'Trần Đức Anh',
                'email' => 'anh.td@emp.hr-management.com',
                'department' => 'PB-KD',
                'code' => 'KD-0001',
                'phone' => '0903000001',
                'address' => 'Quận 3, TP. Hồ Chí Minh',
                'dob' => '1993-01-25',
                'position' => 'Trưởng phòng Kinh doanh',
                'hire_date' => '2020-09-14',
            ],
            [
                'name' => 'Nguyễn Thảo Vy',
                'email' => 'vy.nt@emp.hr-management.com',
                'department' => 'PB-KD',
                'code' => 'KD-0002',
                'phone' => '0903000002',
                'address' => 'Quận Phú Nhuận, TP. Hồ Chí Minh',
                'dob' => '1997-06-17',
                'position' => 'Chuyên viên Kinh doanh',
                'hire_date' => '2022-11-07',
            ],
            [
                'name' => 'Lê Minh Tuấn',
                'email' => 'tuan.lm@emp.hr-management.com',
                'department' => 'PB-KD',
                'code' => 'KD-0003',
                'phone' => '0903000003',
                'address' => 'Quận 10, TP. Hồ Chí Minh',
                'dob' => '1996-12-05',
                'position' => 'Chuyên viên Kinh doanh',
                'hire_date' => '2023-05-15',
            ],
            [
                'name' => 'Bùi Khánh Linh',
                'email' => 'linh.bk@emp.hr-management.com',
                'department' => 'PB-KD',
                'code' => 'KD-0004',
                'phone' => '0903000004',
                'address' => 'Quận Bình Tân, TP. Hồ Chí Minh',
                'dob' => '2000-04-08',
                'position' => 'Nhân viên Kinh doanh',
                'hire_date' => '2025-02-03',
            ],

            // Tài chính - Kế toán
            [
                'name' => 'Nguyễn Thanh Hương',
                'email' => 'huong.nt@emp.hr-management.com',
                'department' => 'PB-TCKT',
                'code' => 'KT-0001',
                'phone' => '0904000001',
                'address' => 'Quận 5, TP. Hồ Chí Minh',
                'dob' => '1991-03-29',
                'position' => 'Kế toán trưởng',
                'hire_date' => '2020-02-10',
            ],
            [
                'name' => 'Phan Thị Mỹ Duyên',
                'email' => 'duyen.ptm@emp.hr-management.com',
                'department' => 'PB-TCKT',
                'code' => 'KT-0002',
                'phone' => '0904000002',
                'address' => 'Quận 8, TP. Hồ Chí Minh',
                'dob' => '1998-10-19',
                'position' => 'Kế toán viên',
                'hire_date' => '2023-08-07',
            ],
            [
                'name' => 'Trương Hoàng Long',
                'email' => 'long.th@emp.hr-management.com',
                'department' => 'PB-TCKT',
                'code' => 'KT-0003',
                'phone' => '0904000003',
                'address' => 'Quận 6, TP. Hồ Chí Minh',
                'dob' => '1996-05-13',
                'position' => 'Chuyên viên Tài chính',
                'hire_date' => '2022-10-03',
            ],

            // Chăm sóc khách hàng
            [
                'name' => 'Đỗ Ngọc Mai',
                'email' => 'mai.dn@emp.hr-management.com',
                'department' => 'PB-CSKH',
                'code' => 'CS-0001',
                'phone' => '0905000001',
                'address' => 'Quận 11, TP. Hồ Chí Minh',
                'dob' => '1997-02-22',
                'position' => 'Trưởng nhóm Chăm sóc khách hàng',
                'hire_date' => '2022-06-13',
            ],
            [
                'name' => 'Nguyễn Quỳnh Trang',
                'email' => 'trang.nq@emp.hr-management.com',
                'department' => 'PB-CSKH',
                'code' => 'CS-0002',
                'phone' => '0905000002',
                'address' => 'Quận 4, TP. Hồ Chí Minh',
                'dob' => '1999-09-14',
                'position' => 'Chuyên viên Chăm sóc khách hàng',
                'hire_date' => '2023-11-20',
            ],
            [
                'name' => 'Trần Nhật Minh',
                'email' => 'minh.tn@emp.hr-management.com',
                'department' => 'PB-CSKH',
                'code' => 'CS-0003',
                'phone' => '0905000003',
                'address' => 'Quận 7, TP. Hồ Chí Minh',
                'dob' => '2001-01-10',
                'position' => 'Nhân viên Chăm sóc khách hàng',
                'hire_date' => '2025-01-06',
            ],

            // Hành chính - Nhân sự
            [
                'name' => 'Ngô Thanh Tâm',
                'email' => 'tam.nt@emp.hr-management.com',
                'department' => 'PB-HCNS',
                'code' => 'HC-0001',
                'phone' => '0906000001',
                'address' => 'Quận Bình Thạnh, TP. Hồ Chí Minh',
                'dob' => '1998-08-02',
                'position' => 'Chuyên viên Hành chính',
                'hire_date' => '2023-03-06',
            ],
            [
                'name' => 'Hoàng Ngọc Yến',
                'email' => 'yen.hn@emp.hr-management.com',
                'department' => 'PB-HCNS',
                'code' => 'HC-0002',
                'phone' => '0906000002',
                'address' => 'Quận Tân Phú, TP. Hồ Chí Minh',
                'dob' => '2000-06-27',
                'position' => 'Nhân viên Hành chính',
                'hire_date' => '2024-06-03',
            ],

            // Nhân viên đã nghỉ việc để test trạng thái
            [
                'name' => 'Lâm Quốc Khánh',
                'email' => 'khanh.lq@emp.hr-management.com',
                'department' => 'PB-KD',
                'code' => 'KD-0005',
                'phone' => '0903000005',
                'address' => 'Quận Tân Bình, TP. Hồ Chí Minh',
                'dob' => '1995-11-30',
                'position' => 'Chuyên viên Kinh doanh',
                'hire_date' => '2022-07-04',
                'employment_status' => 'inactive',
            ],
        ];

        /*
        |--------------------------------------------------------------------------
        | 5. Tạo User + Employee
        |--------------------------------------------------------------------------
        */

        foreach ($employees as $index => $data) {
            $employmentStatus = $data['employment_status'] ?? 'active';

            $userAttributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password,
                'role' => 'employee',
                'account_status' => $employmentStatus === 'active'
                    ? 'active'
                    : 'inactive',
                'email_verified_at' => now(),
            ];
            $user = User::where('email', $userAttributes['email'])
                ->orWhereHas('employee', fn ($query) => $query->where('employee_code', $data['code']))
                ->first();
            $user ? $user->update($userAttributes) : $user = User::create($userAttributes);

            $employee = $user->employee()->updateOrCreate([], [
                'department_id' => $departments[$data['department']]->id,
                'employee_code' => $user->employee?->employee_code ?? $data['code'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'date_of_birth' => $data['dob'],
                'position' => $data['position'],
                'hire_date' => $data['hire_date'],
                'employment_status' => $employmentStatus,
            ]);

            /*
             * Chỉ tạo dữ liệu chấm công cho nhân viên đang hoạt động.
             */
            if ($employmentStatus === 'active') {
                $this->seedAttendances($employee, $index);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Kênh Chat Nội Bộ
        |--------------------------------------------------------------------------
        */
        $this->call(ChatSeeder::class);
    }

    /**
     * Tạo lịch sử chấm công gần thực tế.
     */
    private function seedAttendances($employee, int $employeeIndex): void
    {
        /*
         * Dùng seed cố định để mỗi lần chạy seeder
         * cho kết quả tương đối ổn định.
         */
        mt_srand(20260917 + $employeeIndex);

        $today = Carbon::today();

        for ($day = 1; $day <= 60; $day++) {
            $date = $today->copy()->subDays($day);

            /*
             * Không tạo chấm công thứ 7 và Chủ nhật.
             */
            if ($date->isWeekend()) {
                continue;
            }

            $random = mt_rand(1, 100);

            /*
             * Phân bố gần thực tế:
             *
             * 82% đi làm đúng giờ
             * 10% đi trễ
             * 5% nghỉ phép
             * 3% vắng mặt
             */
            if ($random <= 82) {
                $status = 'present';
            } elseif ($random <= 92) {
                $status = 'late';
            } elseif ($random <= 97) {
                $status = 'leave';
            } else {
                $status = 'absent';
            }

            $checkIn = null;
            $checkOut = null;
            $note = null;

            if ($status === 'present') {
                $checkIn = sprintf(
                    '07:%02d:00',
                    mt_rand(45, 59)
                );

                $checkOut = sprintf(
                    '17:%02d:00',
                    mt_rand(0, 30)
                );
            }

            if ($status === 'late') {
                $checkIn = sprintf(
                    '08:%02d:00',
                    mt_rand(15, 55)
                );

                $checkOut = sprintf(
                    '17:%02d:00',
                    mt_rand(0, 30)
                );

                $note = collect([
                    'Kẹt xe',
                    'Có việc cá nhân buổi sáng',
                    'Đến công ty muộn',
                    null,
                ])->random();
            }

            if ($status === 'leave') {
                $note = collect([
                    'Nghỉ phép năm',
                    'Nghỉ việc cá nhân',
                    'Đã được phê duyệt nghỉ phép',
                ])->random();
            }

            if ($status === 'absent') {
                $note = collect([
                    'Vắng mặt',
                    'Chưa cập nhật lý do nghỉ',
                    null,
                ])->random();
            }

            $employee->attendances()->updateOrCreate([
                'work_date' => $date->toDateString(),
            ], [
                'check_in' => $checkIn,
                'check_out' => $checkOut,
                'status' => $status,
                'note' => $note,
            ]);
        }
    }
}
