<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\ContractExpiringNotification;
use App\Notifications\LeaveRequestApprovedNotification;
use App\Notifications\LeaveRequestRejectedNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

class EmailNotificationTest extends TestCase
{
    private function createUser(string $role, string $email): User
    {
        $user = new User([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => 'Password123!',
            'role' => $role,
            'account_status' => 'active',
        ]);
        $user->setAttribute('id', 1);

        return $user;
    }

    private function createEmployee(User $user): Employee
    {
        $employee = new Employee([
            'user_id' => $user->id,
            'employee_code' => 'EMP-EMAIL-1',
            'position' => 'Staff',
            'hire_date' => '2024-01-01',
            'contract_end_date' => '2026-12-01',
            'employment_status' => 'active',
        ]);
        $employee->setAttribute('id', 1);
        $employee->setRelation('user', $user);

        return $employee;
    }

    public function test_approved_leave_sends_email_notification_to_request_owner(): void
    {
        $hr = $this->createUser('hr', 'hr@example.com');
        $employeeUser = $this->createUser('employee', 'employee@example.com');
        $employee = $this->createEmployee($employeeUser);
        $leave = new LeaveRequest([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Personal leave',
            'status' => 'pending',
        ]);
        $leave->setAttribute('id', 1);
        $notification = new LeaveRequestApprovedNotification($leave);
        $this->assertContains('mail', $notification->via($employeeUser));
        $mail = $notification->toMail($employeeUser);
        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('employee@example.com', $employeeUser->routeNotificationFor('mail'));
        $this->assertSame('Đơn xin nghỉ đã được duyệt', $mail->subject);
        $this->assertStringContainsString('10/11/2026 đến 11/11/2026', implode(' ', $mail->introLines));
    }

    public function test_rejected_leave_email_contains_reason(): void
    {
        $employeeUser = $this->createUser('employee', 'employee@example.com');
        $employee = $this->createEmployee($employeeUser);
        $leave = new LeaveRequest([
            'employee_id' => $employee->id,
            'leave_type' => 'annual',
            'start_date' => '2026-11-10',
            'end_date' => '2026-11-11',
            'reason' => 'Personal leave',
            'status' => 'pending',
        ]);
        $leave->setAttribute('id', 1);
        $leave->review_note = 'Coverage is unavailable.';

        $notification = new LeaveRequestRejectedNotification($leave);
        $this->assertContains('mail', $notification->via($employeeUser));
        $mail = $notification->toMail($employeeUser);
        $this->assertSame('employee@example.com', $employeeUser->routeNotificationFor('mail'));
        $this->assertSame('Đơn xin nghỉ bị từ chối', $mail->subject);
        $this->assertStringContainsString('Coverage is unavailable.', implode(' ', $mail->introLines));
    }

    public function test_contract_expiry_email_goes_to_hr_or_admin_with_contract_details(): void
    {
        $admin = $this->createUser('admin', 'admin@example.com');
        $employeeUser = $this->createUser('employee', 'employee@example.com');
        $employee = $this->createEmployee($employeeUser);
        $notification = new ContractExpiringNotification($employee, 24);

        $this->assertContains('mail', $notification->via($admin));
        $mail = $notification->toMail($admin);
        $this->assertSame('Hợp đồng lao động sắp hết hạn', $mail->subject);
        $this->assertStringContainsString('EMP-EMAIL-1', implode(' ', $mail->introLines));
        $this->assertStringContainsString('24 ngày', implode(' ', $mail->introLines));
        $this->assertSame('admin@example.com', $admin->email);
    }

    public function test_mail_configuration_contains_no_embedded_smtp_password(): void
    {
        $mailConfig = file_get_contents(config_path('mail.php'));
        $this->assertStringNotContainsString('password123', strtolower($mailConfig));
        $this->assertStringNotContainsString('app_password', strtolower($mailConfig));
        $this->assertSame(env('MAIL_PASSWORD'), config('mail.mailers.smtp.password'));
    }
}
