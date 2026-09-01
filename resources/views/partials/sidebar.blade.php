@php
    $role = auth()->user()->role ?? null;
    $link = fn (string $pattern) => request()->routeIs($pattern)
        ? 'group flex items-center gap-3 rounded-xl bg-orange-50 px-3 py-2.5 text-sm font-semibold text-orange-700 ring-1 ring-orange-100'
        : 'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-[var(--app-muted)] hover:bg-orange-50 hover:text-orange-700';
@endphp

<div class="flex min-h-full flex-col">
    <div class="border-b border-[var(--app-border)] px-6 py-6">
        <div class="flex items-center justify-between"><p class="sidebar-label text-xs font-semibold uppercase tracking-[0.2em] text-orange-600">Không gian làm việc</p><button type="button" @click="toggleSidebar()" class="hidden rounded-xl p-2 text-[var(--app-muted)] hover:bg-orange-50 hover:text-orange-600 lg:block" :title="sidebarCollapsed ? 'Mở rộng menu' : 'Thu gọn menu'" aria-label="Thu gọn hoặc mở rộng menu"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M8 6l8 6-8 6"/></svg></button></div>
        <h2 class="sidebar-label mt-2 text-xl font-bold text-[var(--app-text)]">Quản lý nhân sự</h2>
        <p class="sidebar-label mt-1 text-sm text-[var(--app-muted)]">Hệ thống vận hành</p>
    </div>

    <nav class="flex-1 space-y-7 px-4 py-6" aria-label="Điều hướng chính">
        <div>
            <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">Tổng quan</p>
            <div class="mt-2 space-y-1">
                @if ($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" title="Tổng quan" aria-label="Tổng quan" class="{{ $link('admin.dashboard') }}"><span class="sidebar-icon">⌂</span><span class="sidebar-label">Tổng quan</span></a>
                @elseif ($role === 'hr')
                    <a href="{{ route('hr.dashboard') }}" title="Tổng quan" aria-label="Tổng quan" class="{{ $link('hr.dashboard') }}"><span class="sidebar-icon">⌂</span><span class="sidebar-label">Tổng quan</span></a>
                @elseif ($role === 'employee')
                    <a href="{{ route('employee.dashboard') }}" title="Tổng quan" aria-label="Tổng quan" class="{{ $link('employee.dashboard') }}"><span class="sidebar-icon">⌂</span><span class="sidebar-label">Tổng quan</span></a>
                @endif
            </div>
        </div>

        @if (in_array($role, ['admin', 'hr'], true))
            <div>
            <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">Khu vực quản lý</p>
                <div class="mt-2 space-y-1">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.users.index') }}" title="Quản lý tài khoản" aria-label="Quản lý tài khoản" class="{{ $link('admin.users.*') }}"><span class="sidebar-icon">♙</span><span class="sidebar-label">Quản lý tài khoản</span></a>
                    @endif
                    <a href="{{ route('hr.departments.index') }}" title="Phòng ban" aria-label="Phòng ban" class="{{ $link('hr.departments.*') }}"><span class="sidebar-icon">▦</span><span class="sidebar-label">Phòng ban</span></a>
                    <a href="{{ route('hr.employees.index') }}" title="Nhân viên" aria-label="Nhân viên" class="{{ $link('hr.employees.*') }}"><span class="sidebar-icon">♙</span><span class="sidebar-label">Nhân viên</span></a>
                    <a href="{{ route('hr.attendances.index') }}" title="Chấm công" aria-label="Chấm công" class="{{ $link('hr.attendances.*') }}"><span class="sidebar-icon">◷</span><span class="sidebar-label">Chấm công</span></a>
                    <a href="{{ route('me.attendance.index') }}" title="Chấm công của tôi" aria-label="Chấm công của tôi" class="{{ $link('me.attendance.*') }}"><span class="sidebar-icon">◉</span><span class="sidebar-label">Chấm công của tôi</span></a>
                    <a href="{{ route('hr.reports.index') }}" title="Báo cáo" aria-label="Báo cáo" class="{{ $link('hr.reports.*') }}"><span class="sidebar-icon">⌁</span><span class="sidebar-label">Báo cáo</span></a>
                    <a href="{{ route('hr.leave-requests.index') }}" title="Đơn nghỉ" aria-label="Đơn nghỉ" class="{{ $link('hr.leave-requests.*') }}"><span class="sidebar-icon">▣</span><span class="sidebar-label">Đơn nghỉ</span></a>
                </div>
            </div>
        @endif

        @if ($role === 'employee')
            <div>
            <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">Cá nhân</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('employee.profile.show') }}" title="Hồ sơ của tôi" aria-label="Hồ sơ của tôi" class="{{ $link('employee.profile.*') }}"><span class="sidebar-icon">♙</span><span class="sidebar-label">Hồ sơ của tôi</span></a>
                    <a href="{{ route('employee.attendances.index') }}" title="Chấm công của tôi" aria-label="Chấm công của tôi" class="{{ $link('employee.attendances.*') }}"><span class="sidebar-icon">◷</span><span class="sidebar-label">Chấm công của tôi</span></a>
                    <a href="{{ route('me.attendance.index') }}" title="Tự chấm công" aria-label="Tự chấm công" class="{{ $link('me.attendance.*') }}"><span class="sidebar-icon">◉</span><span class="sidebar-label">Tự chấm công</span></a>
                    <a href="{{ route('employee.leave-requests.index') }}" title="Đơn nghỉ của tôi" aria-label="Đơn nghỉ của tôi" class="{{ $link('employee.leave-requests.*') }}"><span class="sidebar-icon">▣</span><span class="sidebar-label">Đơn nghỉ của tôi</span></a>
                </div>
            </div>
        @endif

        @if (in_array($role, ['admin', 'hr'], true))
            <div>
            <p class="sidebar-label px-3 text-xs font-semibold uppercase tracking-wider text-[var(--app-muted)]">Cá nhân</p>
                <div class="mt-2 space-y-1">
                    <a href="{{ route('profile.edit') }}" title="Hồ sơ cá nhân" aria-label="Hồ sơ cá nhân" class="{{ $link('profile.edit') }}"><span class="sidebar-icon">♙</span><span class="sidebar-label">Hồ sơ cá nhân</span></a>
                </div>
            </div>
        @endif
    </nav>

    <div class="sidebar-label border-t border-[var(--app-border)] p-4">
        <div class="rounded-xl bg-orange-50 p-3 text-xs text-orange-700">
            Vai trò hiện tại:
            <span class="font-semibold">{{ ['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'][$role] ?? $role }}</span>
        </div>
    </div>
</div>
