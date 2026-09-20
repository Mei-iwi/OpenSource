@extends('layouts.app')
@section('title', $employee->user->name)
@section('content')
<x-page-header eyebrow="HR / Nhân sự" title="{{ $employee->user->name }}" description="Hồ sơ nhân viên và thông tin tài khoản.">
    <a href="{{ route('hr.employees.edit', $employee) }}" class="app-button-primary">Chỉnh sửa</a>
</x-page-header>

@if(session('created_credentials'))
    @php($credentials = session('created_credentials'))
    <section class="mb-6 rounded-2xl border border-emerald-300 bg-emerald-50 p-5 text-emerald-950 shadow-sm dark:border-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-50" role="status">
        <h2 class="font-bold">Tạo nhân viên và tài khoản thành công</h2>
        <p class="mt-1 text-sm">Thông tin đăng nhập ban đầu được tạo từ ngày sinh. Hãy bàn giao cho nhân viên và yêu cầu đổi mật khẩu sau lần đăng nhập đầu tiên.</p>
        <dl class="mt-4 grid gap-4 sm:grid-cols-3">
            <div><dt class="text-xs opacity-70">Mã nhân viên</dt><dd class="mt-1 font-mono font-bold">{{ $credentials['employee_code'] }}</dd></div>
            <div><dt class="text-xs opacity-70">Email đăng nhập</dt><dd class="mt-1 break-all font-medium">{{ $credentials['email'] }}</dd></div>
            <div><dt class="text-xs opacity-70">Mật khẩu ban đầu</dt><dd class="mt-1 font-mono font-bold">{{ $credentials['initial_password'] }}</dd></div>
        </dl>
    </section>
@endif

<div class="app-panel p-6">
    <div class="mb-6 flex items-center gap-4 border-b border-[var(--app-border)] pb-6">
        <x-user-avatar :user="$employee->user" class="h-20 w-20 rounded-2xl" />
        <div><p class="font-semibold">{{ $employee->user->name }}</p><p class="text-sm text-[var(--app-muted)]">{{ $employee->employee_code }}</p></div>
    </div>
    <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <div><dt class="app-label">Email</dt><dd>{{ $employee->user->email }}</dd></div>
        <div><dt class="app-label">Vai trò</dt><dd>{{ $employee->user->role === 'hr' ? 'Nhân sự' : 'Nhân viên' }}</dd></div>
        <div><dt class="app-label">Ngày sinh</dt><dd>{{ $employee->date_of_birth?->format('d/m/Y') ?: '—' }}</dd></div>
        <div><dt class="app-label">Phòng ban</dt><dd>{{ $employee->department->name }}</dd></div>
        <div><dt class="app-label">Chức vụ</dt><dd>{{ $employee->position ?: '—' }}</dd></div>
        <div><dt class="app-label">Ngày vào làm</dt><dd>{{ $employee->hire_date?->format('d/m/Y') }}</dd></div>
        <div><dt class="app-label">Trạng thái</dt><dd>{{ $employee->employment_status }}</dd></div>
        <div><dt class="app-label">Chấm công</dt><dd>{{ $employee->attendances_count }} bản ghi</dd></div>
    </dl>
</div>
@endsection
