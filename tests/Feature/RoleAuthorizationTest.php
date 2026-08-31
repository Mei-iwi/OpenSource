<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_protected_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_each_role_can_access_its_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/dashboard')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/hr/dashboard')->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'employee']))->get('/employee/dashboard')->assertOk();
    }

    public function test_employee_cannot_access_hr_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'employee']))->get('/hr/dashboard')->assertForbidden();
    }

    public function test_hr_cannot_access_admin_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_can_access_hr_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/hr/dashboard')->assertOk();
    }

    public function test_locked_user_cannot_login(): void
    {
        $user = User::factory()->create(['account_status' => 'locked']);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
