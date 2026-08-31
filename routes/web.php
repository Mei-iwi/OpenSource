<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\HR\DepartmentController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\AttendanceController as HrAttendanceController;
use App\Http\Controllers\HR\ReportController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
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

Route::middleware(['auth', 'account.active', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::patch('/users/{user}/lock', [UserController::class, 'lock'])->name('users.lock');
    Route::patch('/users/{user}/unlock', [UserController::class, 'unlock'])->name('users.unlock');
    Route::resource('users', UserController::class);
});

Route::middleware(['auth', 'account.active', 'role:admin,hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', HrDashboardController::class)->name('dashboard');
    Route::resource('departments', DepartmentController::class);
    Route::resource('employees', EmployeeController::class)->except('destroy');
    Route::resource('attendances', HrAttendanceController::class)->only(['index', 'create', 'store', 'edit', 'update']);
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export.csv', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');
});

Route::middleware(['auth', 'account.active', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', EmployeeDashboardController::class)->name('dashboard');
    Route::get('/profile', [EmployeeProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [EmployeeProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [EmployeeProfileController::class, 'update'])->name('profile.update');
    Route::get('/attendances', [EmployeeAttendanceController::class, 'index'])->name('attendances.index');
});

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::view('/preview/admin', 'dashboard.admin')->name('preview.admin');
Route::view('/preview/hr', 'dashboard.hr')->name('preview.hr');
Route::view('/preview/employee', 'dashboard.employee')->name('preview.employee');

require __DIR__.'/auth.php';
