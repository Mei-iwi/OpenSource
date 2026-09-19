<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Snake Motion') }} — Đăng nhập hệ thống</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script>
        (() => {
            const savedTheme = localStorage.getItem('hr-theme');
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-900 antialiased selection:bg-indigo-200 selection:text-indigo-950 dark:bg-slate-950 dark:text-slate-100">
    <div x-data="{ dark: document.documentElement.classList.contains('dark'), toggleTheme() { this.dark = !this.dark; document.documentElement.classList.toggle('dark', this.dark); localStorage.setItem('hr-theme', this.dark ? 'dark' : 'light'); } }" class="relative flex h-screen min-h-screen w-full flex-col items-center justify-center overflow-hidden bg-gradient-to-br from-sky-50 via-white to-indigo-100 px-4 py-3 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950 sm:py-6">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.9),transparent_55%)] dark:bg-[radial-gradient(circle_at_top,rgba(99,102,241,0.12),transparent_55%)]"></div>

        <!-- Theme Switcher Floating Top Right -->
        <button type="button" @click="toggleTheme()" class="absolute right-5 top-5 z-20 flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white/80 text-slate-600 shadow-sm backdrop-blur-xl transition hover:border-indigo-200 hover:text-indigo-700 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white" :title="dark ? 'Chuyển sang nền sáng' : 'Chuyển sang nền tối'" aria-label="Chuyển đổi giao diện sáng tối">
            <svg x-show="!dark" class="h-4 w-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
            <svg x-show="dark" x-cloak class="h-4 w-4 text-indigo-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>

        <!-- Home Button Floating Top Left -->
        <a href="/" class="absolute left-5 top-5 z-20 flex h-10 w-10 items-center justify-center rounded-2xl border border-slate-200 bg-white/80 text-slate-600 shadow-sm backdrop-blur-xl transition hover:border-indigo-200 hover:text-indigo-700 dark:border-white/10 dark:bg-slate-900/60 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white" title="Trang chủ" aria-label="Trang chủ">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
        </a>

        <!-- Content Container -->
        <div class="relative z-10 my-auto w-full max-w-[560px]">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
