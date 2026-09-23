<?php

use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AttendanceProofController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\Chat\ChatAttachmentController;
use App\Http\Controllers\Chat\ChatChannelController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\Chat\ChatDirectController;
use App\Http\Controllers\Chat\ChatMemberController;
use App\Http\Controllers\Chat\ChatMessageController;
use App\Http\Controllers\Chat\ChatReactionController;
use App\Http\Controllers\Chat\UserProfileCardController;
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
    Route::get('/communications/history', [CommunicationController::class, 'history'])->name('communications.history');
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
    Route::resource('employees', EmployeeController::class)->except('destroy');
    Route::resource('attendances', HrAttendanceController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
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
});

Route::middleware(['auth', 'account.active', 'role:admin,hr,employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::resource('leave-requests', EmployeeLeaveRequestController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/leave-requests/{leave_request}/cancel', [EmployeeLeaveRequestController::class, 'cancel'])->name('leave-requests.cancel');
});

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'account.active'])->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::get('/unread-summary', [ChatController::class, 'unreadSummary'])->name('unread-summary');

    // Quản lý kênh dành cho Admin và HR
    Route::middleware('role:admin,hr')->group(function () {
        Route::get('/channels/create', [ChatChannelController::class, 'create'])->name('channels.create');
        Route::post('/channels', [ChatChannelController::class, 'store'])->name('channels.store');
        Route::get('/channels/{channel:slug}/edit', [ChatChannelController::class, 'edit'])->name('channels.edit');
        Route::patch('/channels/{channel:slug}', [ChatChannelController::class, 'update'])->name('channels.update');
        Route::delete('/channels/{channel:slug}', [ChatChannelController::class, 'destroy'])->name('channels.destroy');

        Route::post('/channels/{channel:slug}/members', [ChatMemberController::class, 'store'])->name('channels.members.store');
        Route::delete('/channels/{channel:slug}/members/{user}', [ChatMemberController::class, 'destroy'])->name('channels.members.destroy');
    });

    // Mở kênh, xem danh sách thành viên, gửi/nhận tin nhắn
    Route::get('/channels/{channel:slug}', [ChatController::class, 'show'])->name('channels.show');
    Route::get('/channels/{channel:slug}/members', [ChatMemberController::class, 'index'])->name('channels.members.index');
    Route::post('/channels/{channel:slug}/read', [ChatController::class, 'markAsRead'])->name('channels.read');
    Route::get('/channels/{channel:slug}/messages', [ChatMessageController::class, 'index'])->name('channels.messages.index');
    Route::post('/channels/{channel:slug}/messages', [ChatMessageController::class, 'store'])->name('channels.messages.store');
    Route::patch('/messages/{message}', [ChatMessageController::class, 'update'])->name('messages.update');
    Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy'])->name('messages.destroy');

    // Tin nhắn riêng 1-1
    Route::post('/direct/{user}', [ChatDirectController::class, 'open'])->name('direct.open');

    // Thẻ hồ sơ công khai an toàn
    Route::get('/users/{user}/profile-card', [UserProfileCardController::class, 'show'])->name('users.profile-card');

    // Tệp đính kèm nội bộ (bảo mật, chống IDOR)
    Route::get('/attachments/{attachment}', [ChatAttachmentController::class, 'show'])->name('attachments.show');

    // Biểu cảm tin nhắn (reactions)
    Route::post('/messages/{message}/reactions', [ChatReactionController::class, 'toggle'])->name('messages.reactions.toggle');
});

require __DIR__.'/auth.php';
