<header class="border-b border-slate-200 bg-white">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" :aria-expanded="mobileMenuOpen" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 lg:hidden" aria-label="Mở menu">☰</button>
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 font-semibold text-slate-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">L</span>
                <span>Luna HR</span>
            </a>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</p>
                <p class="text-xs uppercase tracking-wide text-slate-400">{{ auth()->user()->role }}</p>
            </div>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700" aria-hidden="true">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Đăng xuất
                </button>
            </form>
        </div>
    </div>
</header>
