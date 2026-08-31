<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\EmployeeDashboardController;
use App\Http\Controllers\HrDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
});

Route::middleware(['auth', 'account.active', 'role:admin,hr'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/dashboard', HrDashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'account.active', 'role:employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', EmployeeDashboardController::class)->name('dashboard');
});

Route::middleware(['auth', 'account.active'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
});

// TODO REMOVE AFTER INTEGRATION: preview-only routes retained from Prompt 02.
Route::view('/preview/admin', 'dashboard.admin')->name('preview.admin');
Route::view('/preview/hr', 'dashboard.hr')->name('preview.hr');
Route::view('/preview/employee', 'dashboard.employee')->name('preview.employee');

require __DIR__.'/auth.php';
