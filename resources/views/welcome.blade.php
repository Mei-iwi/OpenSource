<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/ctq-favicon.png') }}">
    @include('partials.theme-init')
    <title>{{ config('app.name') }} — Chấm công và quản trị nhân sự</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="welcome-page min-h-screen antialiased selection:bg-indigo-200 selection:text-indigo-950">
    <header class="border-b border-[var(--app-border)] bg-[var(--app-surface)]">
        <div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-[var(--app-primary-light)]" aria-hidden="true">
                    <img src="{{ asset('images/default-employee.png') }}" alt="" class="h-full w-full object-cover">
                </span>
                <span class="text-sm font-extrabold tracking-tight text-slate-900 sm:text-base">{{ config('app.name') }}</span>
            </a>
            <div class="flex items-center gap-3">
                <x-theme-toggle />
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Bàn làm việc</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">Đăng nhập</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="mx-auto max-w-6xl px-6 pb-20 pt-24 lg:px-8 lg:pt-32">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-indigo-600">Hệ thống quản lý nhân sự</p>
            <h1 class="welcome-title mt-7 max-w-4xl font-extrabold tracking-tight">
                <span>Nhân sự và công việc,</span>
                <span class="welcome-accent">rõ ràng từng ngày.</span>
            </h1>
            <p class="mt-7 max-w-xl text-base leading-relaxed text-[var(--app-muted)] sm:text-lg">
                Theo dõi hồ sơ, chấm công, nghỉ phép và bảng lương tại một nơi.
            </p>
            <div class="mt-9 flex flex-wrap items-center gap-3">
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">Vào hệ thống</a>
            </div>
        </section>

        <section id="notes" class="border-t border-[var(--app-border)] bg-[var(--app-surface)] py-12">
            <div class="mx-auto grid max-w-6xl gap-8 px-6 sm:grid-cols-3 lg:px-8">
                <article class="border-l-2 border-indigo-600 pl-4">
                    <h2 class="text-sm font-semibold text-[var(--app-text)]">Hồ sơ nhân viên</h2>
                    <p class="mt-2 text-sm text-[var(--app-muted)]">Thông tin và hợp đồng tập trung.</p>
                </article>
                <article class="border-l-2 border-[var(--app-border)] pl-4">
                    <h2 class="text-sm font-semibold text-[var(--app-text)]">Chấm công</h2>
                    <p class="mt-2 text-sm text-[var(--app-muted)]">Ghi nhận giờ vào và ra ca.</p>
                </article>
                <article class="border-l-2 border-[var(--app-border)] pl-4">
                    <h2 class="text-sm font-semibold text-[var(--app-text)]">Bảng lương</h2>
                    <p class="mt-2 text-sm text-[var(--app-muted)]">Xem kỳ lương và phiếu lương.</p>
                </article>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 py-7 text-center text-xs text-slate-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>
</body>
</html>
