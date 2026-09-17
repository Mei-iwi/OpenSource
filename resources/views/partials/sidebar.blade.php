@php
    $role = auth()->user()->role ?? null;
    $roleLabels = ['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'];
    $link = fn (string $pattern) => request()->routeIs($pattern)
        ? 'group flex items-center gap-3 rounded-xl bg-indigo-500/10 dark:bg-indigo-500/20 px-3.5 py-2.5 text-sm font-semibold text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 shadow-sm transition-all'
        : 'group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/60 hover:text-slate-900 dark:hover:text-slate-100 transition-all';
@endphp

<div class="flex min-h-full flex-col justify-between">
    <div>
        <!-- Brand Header -->
        <div class="relative overflow-hidden border-b border-blue-100 px-5 py-5 dark:border-blue-900/50">
            <img src="{{ asset('images/sidebar-header.png') }}" alt="Không gian làm việc quản trị nhân sự" class="pointer-events-none absolute inset-0 h-full w-full object-cover object-[center_42%] opacity-40" loading="lazy">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-[var(--app-surface)]/75 via-[var(--app-surface)]/35 to-transparent"></div>
            <div class="relative z-10 flex items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="sidebar-label">
                        <div class="flex items-center gap-1.5">
                            <span class="text-base font-extrabold tracking-tight text-[var(--app-text)]">Snake Motion</span>
                        </div>
                        <p class="text-xs font-medium text-[var(--app-muted)]">Enterprise Platform</p>
                    </div>
                </a>

                <button type="button" @click="toggleSidebar()" class="hidden rounded-lg p-1.5 text-[var(--app-muted)] transition hover:bg-slate-100 hover:text-[var(--app-text)] dark:hover:bg-slate-800 lg:block" :title="sidebarCollapsed ? 'Mở rộng menu' : 'Thu gọn menu'" aria-label="Thu gọn hoặc mở rộng menu">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="space-y-6 px-3.5 py-5" aria-label="Điều hướng chính">
            <!-- Tổng quan -->
            <div>
                <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-[0.1em] text-[var(--app-muted)]">Tổng quan</p>
                <div class="mt-2 space-y-1">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" title="Tổng quan hệ thống" aria-label="Tổng quan hệ thống" class="{{ $link('admin.dashboard') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            </span>
                            <span class="sidebar-label">Tổng quan</span>
                        </a>
                    @elseif ($role === 'hr')
                        <a href="{{ route('hr.dashboard') }}" title="Tổng quan nhân sự" aria-label="Tổng quan nhân sự" class="{{ $link('hr.dashboard') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            </span>
                            <span class="sidebar-label">Tổng quan</span>
                        </a>
                    @elseif ($role === 'employee')
                        <a href="{{ route('employee.dashboard') }}" title="Bàn làm việc" aria-label="Bàn làm việc" class="{{ $link('employee.dashboard') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                            </span>
                            <span class="sidebar-label">Tổng quan</span>
                        </a>
                    @endif
                </div>
            </div>

            @if (in_array($role, ['hr', 'employee'], true))
                <div>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('inbox.index') }}" title="Hộp thư" aria-label="Hộp thư" class="{{ $link('inbox.index') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v12H5.5L4 18.5V5Z"/><path d="M8 9h8M8 13h5"/></svg>
                            </span>
                            <span class="sidebar-label">Hộp thư</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Khu vực Quản lý (Admin / HR) -->
            @if (in_array($role, ['admin', 'hr'], true))
                <div>
                    <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-[0.1em] text-[var(--app-muted)]">Khu vực quản lý</p>
                    <div class="mt-2 space-y-1">
                        @if ($role === 'admin')
                            <a href="{{ route('admin.users.index') }}" title="Quản lý tài khoản" aria-label="Quản lý tài khoản" class="{{ $link('admin.users.*') }}">
                                <span class="sidebar-icon">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                </span>
                                <span class="sidebar-label">Quản lý tài khoản</span>
                            </a>
                            <a href="{{ route('admin.communications.index') }}" title="Thư và quảng cáo" aria-label="Thư và quảng cáo" class="{{ $link('admin.communications.*') }}">
                                <span class="sidebar-icon">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16v12H5.5L4 18.5V4Z"/><path d="M8 8h8M8 12h5"/></svg>
                                </span>
                                <span class="sidebar-label">Thư và quảng cáo</span>
                            </a>
                        @endif

                        <a href="{{ route('hr.departments.index') }}" title="Phòng ban" aria-label="Phòng ban" class="{{ $link('hr.departments.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/><path d="M8 14h.01"/><path d="M16 14h.01"/></svg>
                            </span>
                            <span class="sidebar-label">Phòng ban</span>
                        </a>

                        <a href="{{ route('hr.employees.index') }}" title="Nhân viên" aria-label="Nhân viên" class="{{ $link('hr.employees.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <span class="sidebar-label">Nhân viên</span>
                        </a>

                        <a href="{{ route('hr.attendances.index') }}" title="Chấm công toàn công ty" aria-label="Chấm công toàn công ty" class="{{ $link('hr.attendances.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </span>
                            <span class="sidebar-label">Quản lý chấm công</span>
                        </a>

                        <a href="{{ route('me.attendance.index') }}" title="Chấm công của tôi" aria-label="Chấm công của tôi" class="{{ $link('me.attendance.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            </span>
                            <span class="sidebar-label">Chấm công của tôi</span>
                        </a>

                        <a href="{{ route('hr.reports.index') }}" title="Báo cáo phân tích" aria-label="Báo cáo phân tích" class="{{ $link('hr.reports.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="20" y2="10"/><line x1="18" x2="18" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="16"/></svg>
                            </span>
                            <span class="sidebar-label">Báo cáo phân tích</span>
                        </a>

                        <a href="{{ route('hr.leave-requests.index') }}" title="Đơn nghỉ phép" aria-label="Đơn nghỉ phép" class="{{ $link('hr.leave-requests.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                            </span>
                            <span class="sidebar-label">Đơn nghỉ phép</span>
                        </a>
                    </div>
                </div>
            @endif

            <!-- Khu vực Nhân viên -->
            @if ($role === 'employee')
                <div>
                    <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-[0.1em] text-[var(--app-muted)]">Cá nhân</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('employee.profile.show') }}" title="Hồ sơ của tôi" aria-label="Hồ sơ của tôi" class="{{ $link('employee.profile.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                            </span>
                            <span class="sidebar-label">Hồ sơ cá nhân</span>
                        </a>

                        <a href="{{ route('me.attendance.index') }}" title="Tự chấm công" aria-label="Tự chấm công" class="{{ $link('me.attendance.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            </span>
                            <span class="sidebar-label">Tự chấm công</span>
                        </a>

                        <a href="{{ route('employee.attendances.index') }}" title="Lịch sử chấm công" aria-label="Lịch sử chấm công" class="{{ $link('employee.attendances.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </span>
                            <span class="sidebar-label">Lịch sử chấm công</span>
                        </a>

                        <a href="{{ route('employee.leave-requests.index') }}" title="Đơn nghỉ của tôi" aria-label="Đơn nghỉ của tôi" class="{{ $link('employee.leave-requests.*') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                            </span>
                            <span class="sidebar-label">Đơn nghỉ của tôi</span>
                        </a>
                    </div>
                </div>
            @endif

            @if (in_array($role, ['admin', 'hr'], true))
                <div>
                    <p class="sidebar-label px-3 text-[11px] font-bold uppercase tracking-[0.1em] text-[var(--app-muted)]">Cài đặt</p>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('profile.edit') }}" title="Hồ sơ cá nhân" aria-label="Hồ sơ cá nhân" class="{{ $link('profile.edit') }}">
                            <span class="sidebar-icon">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                            </span>
                            <span class="sidebar-label">Thiết lập tài khoản</span>
                        </a>
                    </div>
                </div>
            @endif
        </nav>
    </div>

</div>
