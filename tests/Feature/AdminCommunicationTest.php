<?php

namespace Tests\Feature;

use App\Mail\AdminMessageMail;
use App\Models\Advertisement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCommunicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_open_communications_page(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $employee = User::where('role', 'employee')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.communications.index'))->assertOk();
        $this->actingAs($employee)->get(route('admin.communications.index'))->assertForbidden();
    }

    public function test_admin_can_send_message_to_selected_role(): void
    {
        $this->seed();
        Mail::fake();
        $admin = User::where('role', 'admin')->firstOrFail();
        $hr = User::where('role', 'hr')->firstOrFail();
        $employee = User::where('role', 'employee')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.communications.messages.send'), [
            'subject' => 'Lịch họp',
            'body' => 'Nhớ xem lịch trong tuần này.',
            'audience' => 'hr',
        ])->assertRedirect();

        Mail::assertSent(AdminMessageMail::class, fn ($mail) => $mail->hasTo($hr->email) && ! $mail->hasTo($employee->email));
        $this->assertDatabaseHas('admin_messages', ['subject' => 'Lịch họp', 'recipient_count' => 2]);
    }

    public function test_admin_can_activate_and_disable_repeating_advertisement(): void
    {
        $this->seed();
        $admin = User::where('role', 'admin')->firstOrFail();
        $employee = User::where('role', 'employee')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.communications.advertisement.save'), [
            'title' => 'Tin vui',
            'message' => 'Bạn làm tốt, công ty tăng trưởng.',
            'display_seconds' => 10,
            'repeat_seconds' => 30,
            'is_active' => 1,
        ])->assertRedirect();

        $ad = Advertisement::firstOrFail();
        $this->actingAs($employee)->get(route('advertisement.active'))->assertJsonPath('active', true);
        $this->actingAs($admin)->patch(route('admin.communications.advertisement.toggle', $ad))->assertRedirect();
        $this->actingAs($employee)->get(route('advertisement.active'))->assertJson(['active' => false]);
    }

    public function test_hr_and_employee_can_read_their_received_messages(): void
    {
        $this->seed();
        Mail::fake();
        $admin = User::where('role', 'admin')->firstOrFail();
        $hr = User::where('role', 'hr')->firstOrFail();
        $employee = User::where('role', 'employee')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.communications.messages.send'), [
            'subject' => 'Thông báo nội bộ',
            'body' => 'Nội dung dành cho HR và nhân viên.',
            'audience' => 'all',
        ])->assertRedirect();

        $this->actingAs($employee)->get(route('inbox.index'))
            ->assertOk()
            ->assertSee('Thông báo nội bộ')
            ->assertSee('Nội dung dành cho HR và nhân viên.');
        $this->actingAs($hr)->get(route('inbox.index'))
            ->assertOk()
            ->assertSee('Thông báo nội bộ')
            ->assertSee('Nội dung dành cho HR và nhân viên.');
    }

    public function test_admin_can_upload_advertisement_image_for_staff_popup(): void
    {
        $this->seed();
        Storage::fake(config('filesystems.avatar_disk'));
        $admin = User::where('role', 'admin')->firstOrFail();
        $employee = User::where('role', 'employee')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.communications.advertisement.save'), [
            'title' => 'Ảnh quảng cáo',
            'message' => 'Một lời nhắc vui.',
            'display_seconds' => 10,
            'repeat_seconds' => 30,
            'is_active' => 1,
            'image' => UploadedFile::fake()->image('campaign.png'),
        ])->assertRedirect();

        $ad = Advertisement::firstOrFail();
        Storage::disk(config('filesystems.avatar_disk'))->assertExists($ad->image_path);
        $this->actingAs($employee)->get(route('advertisement.active'))
            ->assertJsonPath('image_url', route('advertisement.image', $ad));
        $this->actingAs($employee)->get(route('advertisement.image', $ad))->assertOk();
    }
}
