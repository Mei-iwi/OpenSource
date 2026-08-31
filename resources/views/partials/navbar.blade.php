<header class="border-b border-slate-200 bg-white">
    <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden" aria-label="Mở menu">☰</button>
            <a href="{{ route('preview.admin') }}" class="flex items-center gap-2 font-semibold text-slate-900">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 text-sm font-bold text-white">L</span>
                <span>Luna HR</span>
            </a>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden text-sm text-slate-500 sm:inline">Bản xem trước giao diện</span>
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700">A</span>
        </div>
    </div>
</header>
