<header class="sticky top-0 z-40 shrink-0 border-b border-slate-200 bg-white dark:bg-slate-800">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden" aria-label="Mở menu">☰</button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-slate-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-orange-600 text-sm font-bold text-white">HR</span>
                <span>Quản lý nhân sự</span>
            </a>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</p>
                <p class="text-xs tracking-wide text-slate-400">{{ ['admin' => 'Quản trị viên', 'hr' => 'Nhân sự', 'employee' => 'Nhân viên'][auth()->user()->role] ?? auth()->user()->role }}</p>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <button type="button" @click="toggleTheme()" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100" :title="dark ? 'Chuyển sang nền sáng' : 'Chuyển sang nền tối'" x-text="dark ? '☀️ Sáng' : '🌙 Tối'" aria-label="Chuyển đổi giao diện sáng tối"></button>
            <form method="POST" action="{{ route('logout') }}" class="inline" x-data="{ confirmLogout: false }">
                @csrf
                <button type="button" @click="confirmLogout = true" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Đăng xuất
                </button>
                <div x-show="confirmLogout" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" role="dialog" aria-modal="true"><div @click.outside="confirmLogout = false" class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl"><h2 class="text-lg font-bold text-slate-900">Xác nhận đăng xuất</h2><p class="mt-2 text-sm text-slate-600">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p><div class="mt-6 flex justify-end gap-3"><button type="button" @click="confirmLogout = false" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700">Hủy</button><button type="submit" class="rounded-lg bg-orange-600 px-4 py-2 text-sm font-semibold text-white">Đăng xuất</button></div></div></div>
            </form>
        </div>
    </div>
</header>
