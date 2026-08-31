@php
    $role = auth()->user()->role ?? null;
    $link = fn (string $pattern) => request()->routeIs($pattern)
        ? 'block rounded-lg bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700'
        : 'block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700';
@endphp

<div class="flex min-h-full flex-col">
    <div class="border-b border-slate-200 px-6 py-6">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-600">Workspace</p>
        <h2 class="mt-2 text-xl font-bold text-slate-900">Quản lý nhân sự</h2>
        <p class="mt-1 text-sm text-slate-500">Hệ thống vận hành</p>
    </div>

    <nav class="flex-1 space-y-7 px-4 py-6" aria-label="Điều hướng chính">
        <div>
            <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Tổng quan</p>
            <div class="mt-2 space-y-1">
                @if ($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="{{ $link('admin.dashboard') }}">Dashboard Admin</a>
                @elseif ($role === 'hr')
                    <a href="{{ route('hr.dashboard') }}" class="{{ $link('hr.dashboard') }}">Dashboard HR</a>
                @elseif ($role === 'employee')
                    <a href="{{ route('employee.dashboard') }}" class="{{ $link('employee.dashboard') }}">Dashboard Employee</a>
                @endif
            </div>
        </div>

        @if (in_array($role, ['admin', 'hr'], true))
            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Khu vực quản lý</p>
                <div class="mt-2 space-y-1">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.users.index') }}" class="{{ $link('admin.users.*') }}">User Management</a>
                    @endif
                    <a href="{{ route('hr.departments.index') }}" class="{{ $link('hr.departments.*') }}">Departments</a>
                    <a href="{{ route('hr.employees.index') }}" class="{{ $link('hr.employees.*') }}">Employees</a>
                    <a href="{{ route('hr.attendances.index') }}" class="{{ $link('hr.attendances.*') }}">Attendance</a>
                    <a href="{{ route('hr.reports.index') }}" class="{{ $link('hr.reports.*') }}">Reports</a>
                </div>
            </div>
        @endif

        @if ($role === 'employee')
            <div>
                <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Cá nhân</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('employee.profile.show') }}" class="{{ $link('employee.profile.*') }}">My Profile</a>
                    <a href="{{ route('employee.attendances.index') }}" class="{{ $link('employee.attendances.*') }}">My Attendance</a>
                </div>
            </div>
        @endif
    </nav>

    <div class="border-t border-slate-200 p-4">
        <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
            Vai trò hiện tại:
            <span class="font-semibold uppercase text-slate-700">{{ $role }}</span>
        </div>
    </div>
</div>
