<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak] { display: none !important; }</style>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('hr-theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
            const state = localStorage.getItem('hr-nav-state') || (localStorage.getItem('hr-sidebar-collapsed') === 'true' ? 'collapsed' : 'expanded');
            const position = localStorage.getItem('hr-nav-position') || 'left';
            document.documentElement.dataset.navState = ['expanded', 'collapsed', 'hidden'].includes(state) ? state : 'expanded';
            document.documentElement.dataset.navPosition = ['left', 'right', 'top', 'bottom'].includes(position) ? position : 'left';
            document.documentElement.classList.add(`nav-state-${document.documentElement.dataset.navState}`, `nav-position-${document.documentElement.dataset.navPosition}`);
        })();
    </script>
    <title>@yield('title', config('app.name', 'Snake Motion'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="min-h-screen bg-[var(--app-bg)] text-[var(--app-text)] antialiased">
    <div x-data="{ mobileMenuOpen: false, navState: document.documentElement.dataset.navState || 'expanded', navPosition: document.documentElement.dataset.navPosition || 'left', dark: document.documentElement.classList.contains('dark'), setNavState(state) { this.navState = state; localStorage.setItem('hr-nav-state', state); localStorage.setItem('hr-sidebar-collapsed', state === 'collapsed'); this.syncNavClasses(); }, setNavPosition(position) { this.navPosition = position; localStorage.setItem('hr-nav-position', position); this.syncNavClasses(); }, syncNavClasses() { document.documentElement.classList.remove('nav-state-expanded', 'nav-state-collapsed', 'nav-state-hidden', 'nav-position-left', 'nav-position-right', 'nav-position-top', 'nav-position-bottom'); document.documentElement.classList.add('nav-state-' + this.navState, 'nav-position-' + this.navPosition); }, toggleTheme() { this.dark = !this.dark; document.documentElement.classList.toggle('dark', this.dark); localStorage.setItem('hr-theme', this.dark ? 'dark' : 'light'); } }" :class="'nav-state-' + navState + ' nav-position-' + navPosition" class="app-shell min-h-screen">
        @include('partials.navbar')
        <div class="app-body">
        <aside class="desktop-nav hidden h-full w-72 shrink-0 overflow-y-auto border-r border-blue-200 bg-[var(--app-surface)] transition-[width] duration-300 ease-in-out dark:border-blue-900/60 lg:block">@include('partials.sidebar')</aside>
        <div class="app-workspace flex min-w-0 flex-1 flex-col h-full overflow-hidden">
            <div x-show="mobileMenuOpen" x-cloak class="border-b border-[var(--app-border)] bg-[var(--app-surface)] lg:hidden">
                @include('partials.sidebar')
            </div>
            <div class="top-nav border-b border-[var(--app-border)] bg-[var(--app-surface)]">@include('partials.sidebar')</div>
            <main class="app-main min-h-0 flex-1 overflow-y-auto mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
                @include('partials.flash')
                @if (isset($slot)){{ $slot }}@else @yield('content')@endif
            </main>
        </div>
        <div class="bottom-nav border-t border-[var(--app-border)] bg-[var(--app-surface)]">@include('partials.sidebar')</div>
        </div>
    </div>

    @if($activeAdvertisement)
        <div id="company-advertisement" data-sync-url="{{ route('advertisement.active') }}" data-ad-title="{{ $activeAdvertisement->title }}" data-ad-message="{{ $activeAdvertisement->message }}" data-ad-image="{{ $activeAdvertisement->image_path ? route('advertisement.image', $activeAdvertisement) : '' }}" data-display-seconds="{{ $activeAdvertisement->display_seconds }}" data-repeat-seconds="{{ $activeAdvertisement->repeat_seconds }}" class="fixed inset-x-4 bottom-5 z-[90] mx-auto hidden max-w-md rounded-2xl border border-amber-300/50 bg-amber-50 p-4 text-amber-950 shadow-2xl shadow-amber-950/20 dark:border-amber-400/30 dark:bg-amber-950/90 dark:text-amber-50 sm:inset-x-auto sm:right-6 sm:mx-0">
            <img data-ad-image alt="Ảnh quảng cáo" class="mb-3 hidden max-h-48 w-full rounded-xl object-cover">
            <div class="flex items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-400/30 text-lg" aria-hidden="true">📣</div>
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-amber-700 dark:text-amber-300">Thông báo từ công ty</p>
                    <h2 data-ad-title class="mt-1 text-sm font-extrabold">{{ $activeAdvertisement->title }}</h2>
                    <p data-ad-message class="mt-1 text-sm leading-relaxed text-amber-900/80 dark:text-amber-100/80">{{ $activeAdvertisement->message }}</p>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const ad = document.getElementById('company-advertisement');
                if (!ad) return;

                let hideTimer;
                let pollTimer;
                const syncUrl = ad.dataset.syncUrl;

                const showAdvertisement = (payload) => {
                    ad.querySelector('[data-ad-title]').textContent = payload.title;
                    ad.querySelector('[data-ad-message]').textContent = payload.message;
                    const image = ad.querySelector('[data-ad-image]');
                    if (payload.image_url) {
                        image.src = payload.image_url;
                        image.classList.remove('hidden');
                    } else {
                        image.removeAttribute('src');
                        image.classList.add('hidden');
                    }
                    ad.classList.remove('hidden');
                    clearTimeout(hideTimer);
                    hideTimer = setTimeout(() => ad.classList.add('hidden'), Number(payload.display_seconds) * 1000);
                };

                const syncAdvertisement = () => fetch(syncUrl, { headers: { Accept: 'application/json' } })
                    .then((response) => response.json())
                    .then((payload) => {
                        if (!payload.active) {
                            clearInterval(pollTimer);
                            clearTimeout(hideTimer);
                            ad.remove();
                            return;
                        }
                        showAdvertisement(payload);
                    })
                    .catch(() => {});

                showAdvertisement({
                    title: ad.dataset.adTitle,
                    message: ad.dataset.adMessage,
                    display_seconds: ad.dataset.displaySeconds,
                    image_url: ad.dataset.adImage || null,
                });
                syncAdvertisement();
                pollTimer = setInterval(syncAdvertisement, Number(ad.dataset.repeatSeconds) * 1000);
            });
        </script>
    @endif

    <!-- Global Logout Modal (Centered perfectly across entire screen) -->
    <div x-data="{ logoutConfirm: false }" @open-logout.window="logoutConfirm = true" x-show="logoutConfirm" x-cloak class="logout-dialog fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/70 p-4 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="logout-title">
        <div @click.outside="logoutConfirm = false" x-show="logoutConfirm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="w-full max-w-md rounded-2xl border border-[var(--app-border)] bg-[var(--app-surface)] p-6 text-center shadow-2xl transition-all">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-600 ring-8 ring-rose-500/5">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            </div>
            <h2 id="logout-title" class="text-lg font-bold text-[var(--app-text)]">Xác nhận đăng xuất</h2>
            <p class="mt-2 text-sm text-[var(--app-muted)]">Bạn có chắc chắn muốn đăng xuất khỏi hệ thống?</p>
            <div class="mt-6 flex items-center justify-center gap-3">
                <button type="button" @click="logoutConfirm = false" class="min-w-28 rounded-xl border border-[var(--app-border)] px-5 py-2.5 text-sm font-semibold text-[var(--app-text)] transition hover:bg-slate-100 dark:hover:bg-slate-800">Hủy</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="min-w-28 rounded-xl bg-rose-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700 shadow-lg shadow-rose-600/30">Đăng xuất</button>
                </form>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
