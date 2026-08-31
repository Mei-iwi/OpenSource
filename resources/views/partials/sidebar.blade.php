@php
    $role = auth()->user()->role ?? null;
    $link = fn (string $pattern) => request()->routeIs($pattern)
        ? 'block rounded-lg bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700'
        : 'block rounded-lg px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-700';
@endphp

<div class="flex min-h-full flex-col">
    <div class="border-b border-slate-200 px-6 py-6">
        <div class="flex items-center justify-between"><p class="sidebar-label text-xs font-semibold uppercase tracking-[0.2em] text-orange-600">Không gian làm việc</p><button type="button" @click="toggleSidebar()" class="hidden rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:block" :title="sidebarCollapsed ? 'Mở rộng menu' : 'Thu gọn menu'" aria-label="Thu gọn hoặc mở rộng menu">☰</button></div>
        <h2 class="sidebar-label mt-2 text-xl font-bold text-slate-900">Quản lý nhân sự</h2>
        <p class="sidebar-label mt-1 text-sm text-slate-500">Hệ thống vận hành</p>
    </div>

    <nav class="flex-1 space-y-7 px-4 py-6" aria-label="Điều hướng chính">
        <div>
            <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Tổng quan</p>
            <div class="mt-2 space-y-1">
                @if ($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" title="Tổng quan" class="{{ $link('admin.dashboard') }}">📊 <span class="sidebar-label">Tổng quan</span></a>
                @elseif ($role === 'hr')
                    <a href="{{ route('hr.dashboard') }}" title="Tổng quan" class="{{ $link('hr.dashboard') }}">📊 <span class="sidebar-label">Tổng quan</span></a>
                @elseif ($role === 'employee')
                    <a href="{{ route('employee.dashboard') }}" title="Tổng quan" class="{{ $link('employee.dashboard') }}">📊 <span class="sidebar-label">Tổng quan</span></a>
                @endif
            </div>
        </div>

        @if (in_array($role, ['admin', 'hr'], true))
            <div>
                <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Khu vực quản lý</p>
                <div class="mt-2 space-y-1">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.users.index') }}" title="Quản lý tài khoản" class="{{ $link('admin.users.*') }}">👥 <span class="sidebar-label">Quản lý tài khoản</span></a>
                    @endif
                    <a href="{{ route('hr.departments.index') }}" title="Phòng ban" class="{{ $link('hr.departments.*') }}">🏢 <span class="sidebar-label">Phòng ban</span></a>
                    <a href="{{ route('hr.employees.index') }}" title="Nhân viên" class="{{ $link('hr.employees.*') }}">👤 <span class="sidebar-label">Nhân viên</span></a>
                    <a href="{{ route('hr.attendances.index') }}" title="Chấm công" class="{{ $link('hr.attendances.*') }}">🕘 <span class="sidebar-label">Chấm công</span></a>
                    <a href="{{ route('hr.reports.index') }}" title="Báo cáo" class="{{ $link('hr.reports.*') }}">📈 <span class="sidebar-label">Báo cáo</span></a>
                </div>
            </div>
        @endif

        @if ($role === 'employee')
            <div>
                <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Cá nhân</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('employee.profile.show') }}" title="Hồ sơ của tôi" class="{{ $link('employee.profile.*') }}">👤 <span class="sidebar-label">Hồ sơ của tôi</span></a>
                    <a href="{{ route('employee.attendances.index') }}" title="Chấm công của tôi" class="{{ $link('employee.attendances.*') }}">🕘 <span class="sidebar-label">Chấm công của tôi</span></a>
                </div>
            </div>
        @endif

        @if (in_array($role, ['admin', 'hr'], true))
            <div>
                <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Cá nhân</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('profile.edit') }}" title="Hồ sơ cá nhân" class="{{ $link('profile.edit') }}">👤 <span class="sidebar-label">Hồ sơ cá nhân</span></a>
                </div>
            </div>
        @endif
    </nav>

    <div class="sidebar-label border-t border-slate-200 p-4">
        <div class="rounded-xl bg-slate-50 p-3 text-xs text-slate-500">
            Vai trò hiện tại:
            <span class="font-semibold text-slate-700">{{ ['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'][$role] ?? $role }}</span>
        </div>
    </div>
</div>
