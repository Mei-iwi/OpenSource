<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Snake Motion — Chấm công và quản trị nhân sự</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <script>
        (() => {
            const savedTheme = localStorage.getItem('hr-theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased selection:bg-indigo-500 selection:text-white">
    <header class="border-b border-white/10 bg-slate-950/90">
        <div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <span class="snake-avatar-motion flex h-10 w-10 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-tr from-emerald-700 to-lime-500 text-white shadow-lg shadow-emerald-900/30" aria-label="Ảnh rắn chuyển động">
                    <img src="{{ asset('images/default-employee.png') }}" alt="Ảnh rắn chuyển động" class="h-full w-full object-cover">
                </span>
                <span class="text-lg font-extrabold tracking-tight">Snake Motion</span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-500">Bàn làm việc</a>
                @else
                    @if (config('features.public_registration', true) && Route::has('register'))
                        <a href="{{ route('register') }}" class="hidden px-3 py-2 text-sm font-semibold text-slate-300 transition hover:text-white sm:inline-block">Đăng ký</a>
                    @endif
                    <a href="{{ route('login') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-indigo-500">Đăng nhập</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        <section class="mx-auto max-w-4xl px-6 pb-20 pt-24 text-center lg:px-8 lg:pt-32">
            <p class="text-xs font-bold uppercase tracking-[0.28em] text-indigo-300">Một ngày làm việc rất minh bạch</p>
            <h1 class="mx-auto mt-6 max-w-3xl text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-6xl">
                Bạn cống hiến.<br>
                <span class="bg-gradient-to-r from-indigo-300 via-sky-300 to-emerald-300 bg-clip-text text-transparent">Tôi tăng trưởng.</span>
            </h1>
            <p class="mx-auto mt-7 max-w-2xl text-base leading-relaxed text-slate-400 sm:text-lg">
                Sự nỗ lực của bạn là thu nhập của tôi. Snake Motion giúp mọi phút đi làm, giờ tăng ca và lá đơn nghỉ phép được ghi nhận thật gọn gàng.
            </p>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('login') }}" class="rounded-2xl bg-indigo-600 px-6 py-3.5 text-sm font-bold text-white shadow-xl shadow-indigo-600/20 transition hover:bg-indigo-500">Bắt đầu một ngày mới</a>
                <a href="#notes" class="rounded-2xl border border-white/15 bg-white/5 px-6 py-3.5 text-sm font-semibold text-slate-200 transition hover:border-white/25 hover:bg-white/10">Xem bảng ghi nhận</a>
            </div>
        </section>

        <section id="notes" class="border-y border-white/10 bg-slate-900/40 py-14">
            <div class="mx-auto grid max-w-6xl gap-4 px-6 sm:grid-cols-3 lg:px-8">
                <article class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-indigo-300">01 / Hiện diện</p>
                    <h2 class="mt-4 text-lg font-bold text-white">Bạn đúng giờ</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">Bảng công đẹp hơn, báo cáo cuối tháng cũng đẹp hơn.</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-300">02 / Hiệu suất</p>
                    <h2 class="mt-4 text-lg font-bold text-white">Bạn làm thêm</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">Mục tiêu quý này tự nhiên gần hơn một chút.</p>
                </article>
                <article class="rounded-2xl border border-white/10 bg-slate-900/70 p-6">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-300">03 / Kết quả</p>
                    <h2 class="mt-4 text-lg font-bold text-white">Bạn tạo giá trị</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">Tôi có thêm một con số để trình bày trong cuộc họp.</p>
                </article>
            </div>
        </section>

        <section class="mx-auto max-w-3xl px-6 py-20 text-center lg:px-8">
            <p class="text-2xl font-semibold leading-relaxed text-slate-200 sm:text-3xl">“Cứ yên tâm làm việc, hệ thống sẽ nhớ thay bạn.”</p>
            <p class="mt-5 text-sm text-slate-500">Một lời hứa nhỏ từ phòng quản trị nhân sự.</p>
            <a href="{{ route('login') }}" class="mt-8 inline-flex rounded-xl border border-indigo-400/30 bg-indigo-500/10 px-5 py-2.5 text-sm font-bold text-indigo-200 transition hover:bg-indigo-500/20">Đăng nhập hệ thống</a>
        </section>
    </main>

    <footer class="border-t border-white/10 py-7 text-center text-xs text-slate-600">
        &copy; {{ date('Y') }} Snake Motion System
    </footer>
</body>
</html>
