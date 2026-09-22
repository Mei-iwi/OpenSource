<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalChatProfileCardTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 1;

    protected function createUserWithEmployee(Department $department, array $userAttrs = [], array $empAttrs = []): User
    {
        $id = $this->seq++;

        $user = User::create(array_merge([
            'name' => "Profile User {$id}",
            'email' => "profile_user{$id}@example.com",
            'password' => Hash::make('Password123!'),
            'role' => 'employee',
            'account_status' => 'active',
        ], $userAttrs));

        Employee::create(array_merge([
            'user_id' => $user->id,
            'department_id' => $department->id,
            'employee_code' => 'EMP'.str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'phone' => '0988777'.str_pad((string) $id, 3, '0', STR_PAD_LEFT),
            'position' => 'Kỹ sư phần mềm',
            'employment_status' => 'active',
            'hire_date' => now()->toDateString(),
            'address' => '123 Đường Mật, Quận Bí Mật, TP.HCM',
            'date_of_birth' => '1995-05-15',
        ], $empAttrs));

        return $user;
    }

    public function test_user_can_view_coworker_profile_card_with_safe_fields(): void
    {
        $dept = Department::create(['name' => 'Phòng Kỹ Thuật', 'code' => 'TECH_'.uniqid()]);
        $viewer = $this->createUserWithEmployee($dept);
        $target = $this->createUserWithEmployee($dept, ['name' => 'Nguyễn Văn Đồng Nghiệp']);

        $response = $this->actingAs($viewer)->getJson(route('chat.users.profile-card', $target->id));

        $response->assertOk();
        $response->assertJson([
            'id' => $target->id,
            'name' => 'Nguyễn Văn Đồng Nghiệp',
            'email' => $target->email,
            'department' => 'Phòng Kỹ Thuật',
            'position' => 'Kỹ sư phần mềm',
            'can_dm' => true,
        ]);

        $json = $response->json();

        // Safe fields MUST exist
        $this->assertArrayHasKey('phone', $json);
        $this->assertArrayHasKey('avatar_url', $json);
        $this->assertArrayHasKey('role_label', $json);
        $this->assertArrayHasKey('dm_url', $json);

        // Sensitive HR fields MUST NEVER be exposed
        $this->assertArrayNotHasKey('address', $json);
        $this->assertArrayNotHasKey('date_of_birth', $json);
        $this->assertArrayNotHasKey('salary', $json);
        $this->assertArrayNotHasKey('tax_code', $json);
        $this->assertArrayNotHasKey('identity_card_number', $json);
        $this->assertArrayNotHasKey('bank_account', $json);
        $this->assertArrayNotHasKey('insurance', $json);
    }

    public function test_viewing_own_profile_card_disables_direct_message(): void
    {
        $dept = Department::create(['name' => 'Phòng Kỹ Thuật', 'code' => 'TECH_'.uniqid()]);
        $user = $this->createUserWithEmployee($dept);

        $response = $this->actingAs($user)->getJson(route('chat.users.profile-card', $user->id));

        $response->assertOk();
        $response->assertJsonPath('can_dm', false);
    }

    public function test_unauthenticated_user_cannot_view_profile_card(): void
    {
        $dept = Department::create(['name' => 'Phòng Kỹ Thuật', 'code' => 'TECH_'.uniqid()]);
        $target = $this->createUserWithEmployee($dept);

        $response = $this->getJson(route('chat.users.profile-card', $target->id));

        $response->assertUnauthorized();
    }
}
