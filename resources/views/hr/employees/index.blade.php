@extends('layouts.app')
@section('title', 'Nhân viên')
@section('content')
<x-page-header eyebrow="HR / Nhân sự" title="Danh sách nhân viên" description="Tra cứu và quản lý hồ sơ nhân sự."><a href="{{ route('hr.employees.create') }}" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white">+ Tạo nhân viên và tài khoản</a></x-page-header>
<div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"><form method="GET" action="{{ route('hr.employees.index') }}" class="mb-6 grid gap-3 lg:grid-cols-[1fr_auto_auto_auto]" id="employee-filter"><input name="search" value="{{ request('search') }}" type="search" placeholder="Mã, tên hoặc email..." class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><select name="department_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Tất cả phòng ban</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->name }}</option>@endforeach</select><select name="employment_status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm"><option value="">Tất cả trạng thái</option><option value="active" @selected(request('employment_status') === 'active')>Đang làm việc</option><option value="inactive" @selected(request('employment_status') === 'inactive')>Đã nghỉ</option></select><div class="flex gap-2"><button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Lọc</button><a href="{{ route('hr.employees.index') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-slate-500">Đặt lại</a></div></form><div class="grid gap-5 md:grid-cols-2 2xl:grid-cols-3">
    @forelse($employees as $employee)
        <article class="directory-card flex min-w-0 flex-col p-5">
            <div class="flex items-center gap-4"><x-user-avatar :user="$employee->user" class="h-16 w-16 shrink-0" /><div class="min-w-0"><p class="text-xs font-bold tracking-wider text-blue-600 dark:text-blue-300">{{ $employee->employee_code }}</p><h2 class="mt-1 break-words text-lg font-bold">{{ $employee->user->name }}</h2></div></div>
            <dl class="my-5 space-y-3 text-sm"><div><dt class="text-xs text-[var(--app-muted)]">Chức vụ · Phòng ban</dt><dd class="mt-1 font-medium">{{ $employee->position ?: 'Chưa cập nhật' }} · {{ $employee->department->name }}</dd></div><div><dt class="text-xs text-[var(--app-muted)]">Email</dt><dd class="mt-1 break-all">{{ $employee->user->email }}</dd></div></dl>
            <div class="mb-5"><x-status-badge :status="$employee->employment_status" :label="$employee->employment_status === 'active' ? 'Đang làm việc' : 'Đã nghỉ'" /></div>
            <div class="mt-auto flex flex-wrap items-center gap-4 border-t border-[var(--app-border)] pt-4"><a href="{{ route('hr.employees.show', $employee) }}" class="app-button-secondary">Xem hồ sơ</a>@if(auth()->user()->isAdmin() || !$employee->user->isAdmin())<a href="{{ route('hr.employees.edit', $employee) }}" class="font-semibold text-blue-600 dark:text-blue-300">Chỉnh sửa</a>@endif</div>
        </article>
    @empty
        <div class="col-span-full"><x-empty-state title="Không tìm thấy nhân viên" description="Thử thay đổi từ khóa hoặc bộ lọc." /></div>
    @endforelse
</div><div class="mt-6">{{ $employees->links() }}</div></div>
@endsection
