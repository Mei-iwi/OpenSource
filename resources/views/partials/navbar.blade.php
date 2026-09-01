@php
    $roleLabels = ['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'];
    $pageTitle = trim($__env->yieldContent('title')) ?: 'Tổng quan';
    $profileRoute = auth()->user()->isEmployee() ? 'employee.profile.show' : 'profile.edit';
@endphp

<header class="sticky top-0 z-40 shrink-0 border-b border-[var(--app-border)] bg-[var(--app-surface)]/95 shadow-sm backdrop-blur">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen" class="rounded-xl p-2 text-[var(--app-muted)] transition hover:bg-orange-50 hover:text-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 lg:hidden" aria-label="Mở menu">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-semibold text-[var(--app-text)]">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500 text-sm font-bold text-white shadow-sm">HR</span>
                <span class="hidden sm:inline">Quản lý nhân sự</span>
            </a>
            <span class="hidden h-6 w-px bg-[var(--app-border)] sm:block"></span>
            <div class="hidden sm:block">
                <p class="text-sm font-semibold text-[var(--app-text)]">{{ $pageTitle }}</p>
                <p class="text-xs text-[var(--app-muted)]">Không gian làm việc</p>
            </div>
        </div>
        <div class="flex items-center gap-2 sm:gap-3" x-data="{ userMenuOpen: false }">
            <button type="button" @click="toggleTheme()" class="rounded-xl border border-[var(--app-border)] px-3 py-2 text-sm font-medium text-[var(--app-muted)] transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-600" :title="dark ? 'Chuyển sang nền sáng' : 'Chuyển sang nền tối'" x-text="dark ? '☀️ Sáng' : '🌙 Tối'" aria-label="Chuyển đổi giao diện sáng tối"></button>
            <button type="button" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen" class="flex items-center gap-2 rounded-xl p-1.5 transition hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-500" aria-label="Mở menu tài khoản">
                <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-orange-100 text-sm font-bold text-orange-700">
                    @if (auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="Ảnh đại diện {{ auth()->user()->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <span class="hidden text-left sm:block">
                    <span class="block max-w-40 truncate text-sm font-semibold text-[var(--app-text)]">{{ auth()->user()->name }}</span>
                    <span class="block text-xs text-[var(--app-muted)]">{{ $roleLabels[auth()->user()->role] ?? auth()->user()->role }}</span>
                </span>
                <svg class="hidden h-4 w-4 text-[var(--app-muted)] sm:block" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
            </button>
            <div x-show="userMenuOpen" x-cloak @click.outside="userMenuOpen = false" class="absolute right-4 top-14 z-50 w-56 rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-2 shadow-xl sm:right-6 lg:right-8" role="menu">
                <div class="border-b border-[var(--app-border)] px-3 py-2 sm:hidden">
                    <p class="truncate text-sm font-semibold text-[var(--app-text)]">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-[var(--app-muted)]">{{ $roleLabels[auth()->user()->role] ?? auth()->user()->role }}</p>
                </div>
                <a href="{{ route($profileRoute) }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-[var(--app-text)] transition hover:bg-orange-50 hover:text-orange-600" role="menuitem">Hồ sơ cá nhân</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50" role="menuitem">Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>
</header>
