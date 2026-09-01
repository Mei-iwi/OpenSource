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
            <span class="module-icon flex h-9 w-9 items-center justify-center rounded-xl bg-orange-500 text-white shadow-sm" aria-hidden="true"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="5" width="16" height="14" rx="2"/><path stroke-linecap="round" d="M8 9h8M8 13h5M8 17h3"/></svg></span>
            <span class="hidden h-6 w-px bg-[var(--app-border)] sm:block"></span>
            <div class="hidden sm:block">
                <p class="text-sm font-semibold text-[var(--app-text)]">{{ $pageTitle }}</p>
                <p class="text-xs text-[var(--app-muted)]">Không gian làm việc</p>
            </div>
        </div>
        <div class="relative flex items-center gap-2 sm:gap-3" x-data="{ userMenuOpen: false, logoutConfirm: false }">
            <button type="button" x-show="navState === 'hidden'" @click="setNavState('expanded'); mobileMenuOpen = true" class="rounded-xl border border-orange-200 px-3 py-2 text-sm font-semibold text-orange-700 transition hover:bg-orange-50" aria-label="Mở điều hướng">Mở menu</button>
            <button type="button" x-show="navState !== 'hidden'" @click="setNavState(navState === 'expanded' ? 'collapsed' : 'expanded')" class="hidden rounded-xl border border-[var(--app-border)] px-3 py-2 text-xs font-semibold text-[var(--app-text)] transition hover:border-orange-300 hover:bg-orange-50 sm:inline-flex" x-text="navState === 'expanded' ? 'Thu gọn menu' : 'Mở rộng menu'" aria-label="Chuyển trạng thái menu"></button>
            <button type="button" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen" class="user-profile-trigger flex items-center gap-2 rounded-2xl border-2 border-orange-500 bg-[var(--app-surface)] px-2 py-1.5 transition hover:bg-orange-50 focus:outline-none focus:ring-2 focus:ring-orange-500" aria-label="Mở menu tài khoản">
                <span class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full bg-orange-100 text-sm font-bold text-orange-700">
                    @if (auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" alt="Ảnh đại diện {{ auth()->user()->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </span>
                <span class="block max-w-32 text-left sm:max-w-40">
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
                <div class="my-1 border-t border-[var(--app-border)] pt-2"><p class="px-3 py-1 text-xs font-semibold uppercase tracking-wide text-[var(--app-muted)]">Tuỳ chỉnh giao diện</p><div class="grid grid-cols-2 gap-2 px-3 py-2"><button type="button" @click="toggleTheme()" class="rounded-lg border border-[var(--app-border)] px-2 py-2 text-xs font-semibold text-[var(--app-text)]" x-text="dark ? 'Nền sáng' : 'Nền tối'"></button><button type="button" @click="setNavState(navState === 'expanded' ? 'collapsed' : 'expanded')" class="rounded-lg border border-[var(--app-border)] px-2 py-2 text-xs font-semibold text-[var(--app-text)]" x-text="navState === 'collapsed' ? 'Mở rộng' : 'Thu gọn'"></button></div><div class="px-3 pb-2"><label class="block text-xs font-medium text-[var(--app-muted)]" for="nav-position">Vị trí menu</label><select id="nav-position" x-model="navPosition" @change="setNavPosition($event.target.value)" class="mt-1 w-full rounded-lg border border-[var(--app-border)] bg-[var(--app-surface)] px-2 py-1.5 text-xs text-[var(--app-text)]"><option value="left">Bên trái</option><option value="right">Bên phải</option><option value="top">Phía trên</option><option value="bottom">Phía dưới</option></select></div><div class="grid grid-cols-3 gap-1 px-3 pb-2"><button type="button" @click="setNavState('expanded')" class="rounded-lg border border-[var(--app-border)] px-1 py-1.5 text-xs text-[var(--app-text)]">Mở rộng</button><button type="button" @click="setNavState('collapsed')" class="rounded-lg border border-[var(--app-border)] px-1 py-1.5 text-xs text-[var(--app-text)]">Thu gọn</button><button type="button" @click="setNavState('hidden')" class="rounded-lg border border-[var(--app-border)] px-1 py-1.5 text-xs text-[var(--app-text)]">Ẩn menu</button></div></div>
                <button type="button" @click="logoutConfirm = true; userMenuOpen = false" class="w-full rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50" role="menuitem">Đăng xuất</button>
            </div>
            <div x-show="logoutConfirm" x-cloak class="logout-dialog fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="logout-title">
                <div @click.outside="logoutConfirm = false" class="w-full max-w-md rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 shadow-2xl">
                    <h2 id="logout-title" class="text-lg font-bold text-[var(--app-text)]">Xác nhận đăng xuất</h2>
                    <p class="mt-2 text-sm text-[var(--app-muted)]">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="logoutConfirm = false" class="rounded-xl border border-[var(--app-border)] px-4 py-2.5 text-sm font-semibold text-[var(--app-text)] transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500">Hủy</button>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
