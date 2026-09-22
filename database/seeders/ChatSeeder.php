<?php

namespace Database\Seeders;

use App\Models\ChatChannel;
use App\Models\ChatChannelMember;
use App\Models\ChatMessage;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin?->id;

        // 1. Kênh Toàn công ty mặc định
        $companyChannel = ChatChannel::firstOrCreate(
            ['slug' => 'cong-ty'],
            [
                'name' => 'Toàn công ty',
                'slug' => 'cong-ty',
                'description' => 'Kênh trao đổi chung cho toàn thể nhân sự trong công ty.',
                'type' => 'company',
                'department_id' => null,
                'created_by' => $adminId,
                'is_default' => true,
            ]
        );

        // Gán tất cả tài khoản đang hoạt động vào kênh Toàn công ty
        $activeUsers = User::where('account_status', 'active')->get();
        foreach ($activeUsers as $user) {
            ChatChannelMember::firstOrCreate(
                ['channel_id' => $companyChannel->id, 'user_id' => $user->id],
                ['joined_at' => now(), 'last_read_at' => now()]
            );
        }

        // Tạo tin nhắn chào mừng trong kênh công ty nếu chưa có tin nhắn nào
        if ($admin && $companyChannel->messages()->count() === 0) {
            ChatMessage::create([
                'channel_id' => $companyChannel->id,
                'user_id' => $admin->id,
                'message' => 'Chào mừng toàn thể nhân sự đến với hệ thống giao tiếp và chat nội bộ của công ty!',
                'created_at' => now()->subHours(2),
            ]);
        }

        // 2. Kênh theo từng phòng ban
        $departments = Department::with('employees.user')->get();
        foreach ($departments as $department) {
            $slug = Str::slug($department->name);
            $deptChannel = ChatChannel::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $department->name,
                    'slug' => $slug,
                    'description' => 'Kênh trao đổi công việc nội bộ phòng '.$department->name.'.',
                    'type' => 'department',
                    'department_id' => $department->id,
                    'created_by' => $adminId,
                    'is_default' => false,
                ]
            );

            // Gán nhân viên phòng ban vào kênh
            foreach ($department->employees as $employee) {
                if ($employee->user && $employee->user->account_status === 'active') {
                    ChatChannelMember::firstOrCreate(
                        ['channel_id' => $deptChannel->id, 'user_id' => $employee->user_id],
                        ['joined_at' => now(), 'last_read_at' => now()]
                    );
                }
            }

            // Gán Admin vào kênh phòng ban để quản trị nếu cần
            if ($admin) {
                ChatChannelMember::firstOrCreate(
                    ['channel_id' => $deptChannel->id, 'user_id' => $admin->id],
                    ['joined_at' => now(), 'last_read_at' => now()]
                );
            }
        }

        // 3. Kênh Nhóm dự án mẫu
        $groupChannel = ChatChannel::firstOrCreate(
            ['slug' => 'du-an-website'],
            [
                'name' => 'Dự án Website',
                'slug' => 'du-an-website',
                'description' => 'Kênh thảo luận dự án nâng cấp website nội bộ và cổng thông tin.',
                'type' => 'group',
                'department_id' => null,
                'created_by' => $adminId,
                'is_default' => false,
            ]
        );

        if ($admin) {
            ChatChannelMember::firstOrCreate(
                ['channel_id' => $groupChannel->id, 'user_id' => $admin->id],
                ['joined_at' => now(), 'last_read_at' => now()]
            );
        }

        // Thêm nhân sự CNTT vào nhóm dự án
        $itDept = Department::where('code', 'PB-CNTT')->with('employees.user')->first();
        if ($itDept) {
            foreach ($itDept->employees as $emp) {
                if ($emp->user) {
                    ChatChannelMember::firstOrCreate(
                        ['channel_id' => $groupChannel->id, 'user_id' => $emp->user_id],
                        ['joined_at' => now(), 'last_read_at' => now()]
                    );
                }
            }
        }
    }
}
