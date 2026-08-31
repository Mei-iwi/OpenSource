<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_users_index(): void
    {
        $this->actingAs($this->admin())->get('/admin/users')->assertOk();
    }

    public function test_non_admin_cannot_view_users_index(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'hr']))->get('/admin/users')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'employee']))->get('/admin/users')->assertForbidden();
    }

    public function test_admin_can_create_hr_and_employee_accounts(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New HR', 'email' => 'new-hr@example.com', 'role' => 'hr',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'new-hr@example.com', 'role' => 'hr', 'account_status' => 'active']);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New Employee', 'email' => 'new-employee@example.com', 'role' => 'employee',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['email' => 'new-employee@example.com', 'role' => 'employee']);
    }

    public function test_duplicate_email_is_rejected(): void
    {
        $admin = $this->admin();
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Duplicate', 'email' => 'duplicate@example.com', 'role' => 'hr',
            'password' => 'Password123!', 'password_confirmation' => 'Password123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_lock_and_unlock_account(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create(['role' => 'employee']);

        $this->actingAs($admin)->patch("/admin/users/{$user->id}/lock")->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'account_status' => 'locked']);
        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->actingAs($admin)->patch("/admin/users/{$user->id}/unlock")->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'account_status' => 'active']);
    }

    public function test_admin_cannot_demote_self(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name, 'email' => $admin->email, 'role' => 'hr',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'role' => 'admin']);
    }
}
