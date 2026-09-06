<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Luna HR — Hệ thống Quản trị Nhân sự & Chấm công Đẳng cấp</title>
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
<body class="bg-slate-950 text-slate-100 selection:bg-indigo-500 selection:text-white antialiased">
    <!-- Ambient Background Lighting Orbs -->
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-40 -top-40 h-[550px] w-[550px] rounded-full bg-indigo-600/20 blur-[130px]"></div>
        <div class="absolute right-0 top-1/4 h-[450px] w-[450px] rounded-full bg-sky-500/15 blur-[120px]"></div>
        <div class="absolute bottom-0 left-1/3 h-[500px] w-[500px] rounded-full bg-purple-600/15 blur-[140px]"></div>
    </div>

    <!-- Header Navigation -->
    <header class="sticky top-0 z-50 border-b border-white/10 bg-slate-950/75 backdrop-blur-xl">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-400 text-white shadow-lg shadow-indigo-500/30 ring-2 ring-indigo-400/20">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
>>>>>>> Stashed changes
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Luna<span class="text-indigo-400">HR</span></span>
                    <span class="ml-1.5 rounded-full bg-indigo-500/20 px-2 py-0.5 text-[10px] font-bold text-indigo-300 border border-indigo-500/30">Enterprise</span>
                </div>
            </a>

            <nav class="hidden items-center gap-8 text-sm font-semibold text-slate-300 md:flex">
                <a href="#features" class="transition hover:text-white">Tính năng</a>
                <a href="#stats" class="transition hover:text-white">Hiệu năng</a>
                <a href="#security" class="transition hover:text-white">Bảo mật</a>
            </nav>

            <div class="flex items-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-500 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:from-indigo-600 hover:to-indigo-700 hover:scale-[1.02]">
                        <span>Bàn làm việc</span>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-600 to-sky-500 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/30 transition hover:scale-[1.02] hover:shadow-indigo-500/50">
                        <span>Đăng nhập</span>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main class="relative z-10">
        <section class="mx-auto max-w-7xl px-6 pt-16 pb-24 text-center lg:px-8 lg:pt-24">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 rounded-full border border-indigo-500/30 bg-indigo-500/10 px-4 py-1.5 text-xs font-semibold text-indigo-300 backdrop-blur-md shadow-xs">
                <span class="flex h-2 w-2 rounded-full bg-indigo-400 animate-pulse"></span>
                <span>Thế Hệ Quản Trị Nhân Sự & Chấm Công 4.0</span>
            </div>

            <!-- Main Heading -->
            <h1 class="mx-auto mt-8 max-w-4xl text-4xl font-extrabold tracking-tight sm:text-6xl lg:text-7xl leading-[1.15]">
                Tối ưu vận hành, <br>
                <span class="bg-gradient-to-r from-indigo-400 via-sky-300 to-emerald-400 bg-clip-text text-transparent">
                    nâng tầm doanh nghiệp
                </span> của bạn.
            </h1>

            <!-- Subtitle -->
            <p class="mx-auto mt-6 max-w-2xl text-base text-slate-400 sm:text-lg lg:text-xl font-normal leading-relaxed">
                Giải pháp số hóa toàn diện: Quản lý nhân sự tập trung, chấm công camera thông minh, tự động hóa đơn nghỉ phép và báo cáo chuyên cần đa chiều theo thời gian thực.
            </p>

            <!-- CTA Buttons (No Arrows) -->
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <a href="{{ route('login') }}" class="inline-flex items-center rounded-2xl bg-indigo-600 px-7 py-3.5 text-base font-bold text-white shadow-xl shadow-indigo-600/30 transition hover:bg-indigo-500 hover:scale-105 hover:shadow-indigo-500/50">
                    <span>Trải nghiệm ngay</span>
                </a>
                <a href="#features" class="inline-flex items-center rounded-2xl border border-white/15 bg-white/5 px-6 py-3.5 text-base font-semibold text-slate-200 backdrop-blur-md transition hover:bg-white/10 hover:border-white/30">
                    <span>Khám phá tính năng</span>
                </a>
            </div>

            <!-- Product Showcase / Interactive Mockup Preview -->
            <div class="mt-16 rounded-3xl border border-white/15 bg-slate-900/60 p-3 sm:p-5 shadow-2xl backdrop-blur-2xl ring-1 ring-white/10">
                <div class="rounded-2xl border border-white/10 bg-slate-950 p-6 sm:p-8 text-left">
                    <!-- Top Window Controls -->
                    <div class="flex items-center justify-between border-b border-white/10 pb-5">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-rose-500/80"></span>
                            <span class="h-3 w-3 rounded-full bg-amber-500/80"></span>
                            <span class="h-3 w-3 rounded-full bg-emerald-500/80"></span>
                            <span class="ml-3 text-xs font-mono text-slate-400">dashboard.lunahr.internal</span>
                        </div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 px-3 py-1 text-xs font-semibold text-emerald-400">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            Hệ thống hoạt động ổn định
                        </span>
                    </div>

                    <!-- Mini Mockup Dashboard Grid -->
                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                            <p class="text-xs font-medium text-slate-400">Tổng nhân viên</p>
                            <p class="mt-2 text-2xl font-extrabold text-white">128</p>
                            <p class="mt-1 text-xs text-indigo-400 font-semibold">↑ +8% tháng này</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                            <p class="text-xs font-medium text-slate-400">Có mặt hôm nay</p>
                            <p class="mt-2 text-2xl font-extrabold text-emerald-400">121</p>
                            <p class="mt-1 text-xs text-emerald-400/80 font-medium">94.5% tỉ lệ đi làm</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                            <p class="text-xs font-medium text-slate-400">Đi muộn</p>
                            <p class="mt-2 text-2xl font-extrabold text-amber-400">4</p>
                            <p class="mt-1 text-xs text-amber-400/80 font-medium">Trung bình 12 phút</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-slate-900/70 p-4">
                            <p class="text-xs font-medium text-slate-400">Đơn nghỉ chờ duyệt</p>
                            <p class="mt-2 text-2xl font-extrabold text-sky-400">3</p>
                            <p class="mt-1 text-xs text-sky-400/80 font-medium">Cần xử lý trong ngày</p>
                        </div>
                    </div>

                    <!-- Mockup Feed -->
                    <div class="mt-5 rounded-2xl border border-white/10 bg-slate-900/40 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-white">Nhật ký chấm công tức thì (Real-time Stream)</h3>
                            <span class="text-xs text-slate-400">Cập nhật mỗi giây</span>
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between rounded-xl bg-white/5 p-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600/30 text-xs font-bold text-indigo-300">NV</span>
                                    <div>
                                        <p class="text-xs font-bold text-white">Nguyễn Văn An · EMP-0014</p>
                                        <p class="text-[11px] text-slate-400">Phòng Công nghệ thông tin</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono text-slate-300">07:58:21</span>
                                    <span class="rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2.5 py-0.5 text-[11px] font-semibold">Đúng giờ</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between rounded-xl bg-white/5 p-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-600/30 text-xs font-bold text-sky-300">TH</span>
                                    <div>
                                        <p class="text-xs font-bold text-white">Trần Thị Hương · EMP-0023</p>
                                        <p class="text-[11px] text-slate-400">Phòng Hành chính - Nhân sự</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-xs font-mono text-slate-300">08:01:05</span>
                                    <span class="rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 px-2.5 py-0.5 text-[11px] font-semibold">Đúng giờ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bento Grid Features Section -->
        <section id="features" class="border-t border-white/10 bg-slate-900/30 py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-400">Tính năng vượt trội</h2>
                    <p class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Trải nghiệm quản trị thông minh không rào cản</p>
                </div>

                <div class="mt-16 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <!-- Feature 1 -->
                    <div class="group rounded-3xl border border-white/10 bg-slate-900/60 p-8 backdrop-blur-xl transition hover:border-indigo-500/50 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-500/15 text-indigo-400 ring-1 ring-indigo-500/30 mb-6">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Chấm công Camera AI</h3>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">Điểm danh trực quan bằng camera thiết bị hoặc tải ảnh chứng thực. Bảo mật vị trí và minh bạch dữ liệu.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="group rounded-3xl border border-white/10 bg-slate-900/60 p-8 backdrop-blur-xl transition hover:border-indigo-500/50 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-500/15 text-sky-400 ring-1 ring-sky-500/30 mb-6">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="16" height="20" x="4" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M8 10h.01"/><path d="M16 10h.01"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Cơ cấu Phòng ban</h3>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">Sơ đồ tổ chức linh hoạt, phân bổ nhân sự chính xác theo phòng ban, chức vụ và cấp bậc quản lý.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="group rounded-3xl border border-white/10 bg-slate-900/60 p-8 backdrop-blur-xl transition hover:border-indigo-500/50 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-500/15 text-emerald-400 ring-1 ring-emerald-500/30 mb-6">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Báo cáo & Phân tích</h3>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">Biểu đồ trực quan xu hướng đi muộn, vắng mặt 6 tháng và xuất dữ liệu báo cáo ra file CSV/Excel tiện lợi.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="group rounded-3xl border border-white/10 bg-slate-900/60 p-8 backdrop-blur-xl transition hover:border-indigo-500/50 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-500/15 text-amber-400 ring-1 ring-amber-500/30 mb-6">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Duyệt Đơn Nghỉ Phép</h3>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">Quy trình nộp và phê duyệt đơn nghỉ trực tuyến nhanh gọn, thông báo tự động và cập nhật tức thì vào bảng công.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="group rounded-3xl border border-white/10 bg-slate-900/60 p-8 backdrop-blur-xl transition hover:border-indigo-500/50 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-purple-500/15 text-purple-400 ring-1 ring-purple-500/30 mb-6">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Phân Quyền Đa Tầng</h3>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">Tách bạch nghiêm ngặt quyền hạn giữa Quản trị viên (Admin), Chuyên viên nhân sự (HR) và Nhân viên.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="group rounded-3xl border border-white/10 bg-slate-900/60 p-8 backdrop-blur-xl transition hover:border-indigo-500/50 hover:bg-slate-900/90">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-500/15 text-rose-400 ring-1 ring-rose-500/30 mb-6">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m10 15 5-3-5-3v6Z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white">Trải Nghiệm Mượt Mà</h3>
                        <p class="mt-2 text-sm text-slate-400 leading-relaxed">Hỗ trợ chế độ Sáng / Tối cao cấp, điều hướng linh hoạt 4 hướng (trái, phải, trên, dưới) tuỳ biến cá nhân.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Counter Section -->
        <section id="stats" class="border-t border-white/10 py-20 bg-slate-950">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-8 text-center md:grid-cols-4">
                    <div>
                        <p class="text-4xl font-extrabold text-indigo-400 lg:text-5xl">99.99%</p>
                        <p class="mt-2 text-sm text-slate-400">Thời gian hoạt động</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-sky-400 lg:text-5xl">&lt; 0.2s</p>
                        <p class="mt-2 text-sm text-slate-400">Tốc độ ghi nhận chấm công</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-emerald-400 lg:text-5xl">100%</p>
                        <p class="mt-2 text-sm text-slate-400">Minh bạch hồ sơ</p>
                    </div>
                    <div>
                        <p class="text-4xl font-extrabold text-purple-400 lg:text-5xl">24/7</p>
                        <p class="mt-2 text-sm text-slate-400">Sẵn sàng vận hành</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Final CTA -->
        <section class="border-t border-white/10 py-24 bg-gradient-to-b from-slate-900/50 to-slate-950 text-center">
            <div class="mx-auto max-w-3xl px-6 lg:px-8">
                <h2 class="text-3xl font-extrabold text-white sm:text-5xl">Sẵn sàng nâng tầm chuyển đổi số nhân sự?</h2>
                <p class="mt-4 text-slate-400 text-base sm:text-lg">Đăng nhập vào hệ thống Luna HR ngay hôm nay để quản lý đội ngũ hiệu quả và tinh gọn nhất.</p>
                <div class="mt-8">
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-2xl bg-indigo-600 px-8 py-4 text-base font-bold text-white shadow-xl shadow-indigo-600/30 transition hover:bg-indigo-500 hover:scale-105">
                        <span>Đăng nhập hệ thống ngay</span>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-white/10 bg-slate-950 py-10 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} Luna HR Management System. All rights reserved.</p>
    </footer>
</body>
</html>
