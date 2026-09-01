<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>[x-cloak] { display: none !important; }</style>
    <script>
        (() => {
            const state = localStorage.getItem('hr-nav-state') || (localStorage.getItem('hr-sidebar-collapsed') === 'true' ? 'collapsed' : 'expanded');
            const position = localStorage.getItem('hr-nav-position') || 'left';
            document.documentElement.dataset.navState = ['expanded', 'collapsed', 'hidden'].includes(state) ? state : 'expanded';
            document.documentElement.dataset.navPosition = ['left', 'right', 'top', 'bottom'].includes(position) ? position : 'left';
            document.documentElement.classList.add(`nav-state-${document.documentElement.dataset.navState}`, `nav-position-${document.documentElement.dataset.navPosition}`);
        })();
    </script>
    <title>@yield('title', config('app.name', 'Luna HR'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('styles')
</head>
<body class="min-h-screen bg-[var(--app-bg)] text-[var(--app-text)] antialiased">
    <div x-data="{ mobileMenuOpen: false, navState: document.documentElement.dataset.navState || 'expanded', navPosition: document.documentElement.dataset.navPosition || 'left', sidebarCollapsed: (document.documentElement.dataset.navState || 'expanded') === 'collapsed', dark: document.documentElement.classList.contains('dark'), toggleSidebar() { this.setNavState(this.navState === 'collapsed' ? 'expanded' : 'collapsed'); }, setNavState(state) { this.navState = state; this.sidebarCollapsed = state === 'collapsed'; localStorage.setItem('hr-nav-state', state); localStorage.setItem('hr-sidebar-collapsed', state === 'collapsed'); this.syncNavClasses(); }, setNavPosition(position) { this.navPosition = position; localStorage.setItem('hr-nav-position', position); this.syncNavClasses(); }, syncNavClasses() { document.documentElement.classList.remove('nav-state-expanded', 'nav-state-collapsed', 'nav-state-hidden', 'nav-position-left', 'nav-position-right', 'nav-position-top', 'nav-position-bottom'); document.documentElement.classList.add('nav-state-' + this.navState, 'nav-position-' + this.navPosition); }, toggleTheme() { this.dark = !this.dark; document.documentElement.classList.toggle('dark', this.dark); localStorage.setItem('hr-theme', this.dark ? 'dark' : 'light'); } }" :class="'nav-state-' + navState + ' nav-position-' + navPosition" class="app-shell min-h-screen">
        <aside class="desktop-nav hidden h-screen w-72 shrink-0 overflow-y-auto border-r border-[var(--app-border)] bg-[var(--app-surface)] transition-[width] duration-300 ease-in-out lg:block">@include('partials.sidebar')</aside>
        <div class="app-workspace flex min-w-0 flex-1 flex-col lg:h-screen lg:overflow-hidden">
            @include('partials.navbar')
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
    @stack('scripts')
</body>
</html>
