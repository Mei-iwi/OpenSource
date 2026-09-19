<?php

use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AttendanceProofController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\LeaveRequestController as EmployeeLeaveRequestController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\HR\AttendanceController as HrAttendanceController;
use App\Http\Controllers\HR\DepartmentController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\LeaveRequestController as HrLeaveRequestController;
use App\Http\Controllers\HR\ReportController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SelfAttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'));

Route::get('/dashboard', function () {
    return match (true) {
        auth()->user()->isAdmin() => redirect()->route('admin.dashboard'),
        auth()->user()->isHr() => redirect()->route('hr.dashboard'),
        auth()->user()->isEmployee() => redirect()->route('employee.dashboard'),
        default => abort(403),
    };
})->middleware(['auth', 'account.active'])->name('dashboard');

Route::middleware(['auth', 'account.active'])->prefix('me')->name('me.')->group(function () {
    Route::get('/attendance', [SelfAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-in', [SelfAttendanceController::class, 'checkIn'])->name('attendance.check-in');
    Route::post('/attendance/check-out', [SelfAttendanceController::class, 'checkOut'])->name('attendance.check-out');
});

Route::middleware(['auth', 'account.active'])->get('/attendance/{attendance}/proof/{type}', [AttendanceProofController::class, 'show'])->name('attendance.proof');
Route::middleware(['auth', 'account.active'])->get('/avatar/{user}', [AvatarController::class, 'show'])->name('avatar.show');
Route::middleware(['auth', 'account.active', 'role:admin,hr,employee'])
    ->get('/advertisement/{advertisement}/image', [CommunicationController::class, 'showAdvertisementImage'])
    ->name('advertisement.image');
Route::middleware(['auth', 'account.active', 'role:hr,employee'])
    ->get('/inbox', [InboxController::class, 'index'])
    ->name('inbox.index');

Route::middleware(['auth', 'account.active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::get('/communications', [CommunicationController::class, 'index'])->name('communications.index');
    Route::post('/communications/messages', [CommunicationController::class, 'sendMessage'])->name('communications.messages.send');
    Route::post('/communications/advertisement', [CommunicationController::class, 'saveAdvertisement'])->name('communications.advertisement.save');
    Route::patch('/communications/advertisement/{advertisement}/toggle', [CommunicationController::class, 'toggleAdvertisement'])->name('communications.advertisement.toggle');
    Route::patch('/users/{user}/lock', [UserController::class, 'lock'])->name('users.lock');
    Route::patch('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->middleware('throttle:3,1')->name('users.reset-password');
    Route::redirect('/users/create', '/hr/employees/create');
    Route::resource('users', UserController::class)->except(['create', 'store']);
});

Route::middleware(['auth', 'account.active', 'role:hr,employee'])
    ->get('/advertisement/active', [CommunicationController::class, 'activeAdvertisement'])
    ->name('advertisement.active');

Route::middleware(['auth', 'account.active', 'role:admin,hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', HrDashboardController::class)->name('dashboard');
    Route::resource('departments', DepartmentController::class);
    Route::get('/employees/code-preview', [EmployeeController::class, 'codePreview'])->name('employees.code-preview');
    Route::resource('employees', EmployeeController::class)->except('destroy');
    Route::resource('attendances', HrAttendanceController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export.csv', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
    Route::get('/leave-requests', [HrLeaveRequestController::class, 'index'])->name('leave-requests.index');
    Route::get('/leave-requests/{leave_request}', [HrLeaveRequestController::class, 'show'])->name('leave-requests.show');
    Route::patch('/leave-requests/{leave_request}/review', [HrLeaveRequestController::class, 'review'])->name('leave-requests.review');
});

Route::middleware(['auth', 'account.active', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', EmployeeDashboardController::class)->name('dashboard');
    Route::get('/attendances', [EmployeeAttendanceController::class, 'index'])->name('attendances.index');
    Route::resource('leave-requests', EmployeeLeaveRequestController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/leave-requests/{leave_request}/cancel', [EmployeeLeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
});

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

require __DIR__.'/auth.php';
