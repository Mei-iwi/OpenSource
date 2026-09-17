<x-guest-layout>
    <!-- Outer Card Container with overflow-hidden to prevent spinning gradient diamond from overflowing -->
    <div class="login-card-container relative z-10 w-full overflow-hidden rounded-[1.75rem] p-[2px] shadow-xl shadow-indigo-950/50">
        <!-- Spinning Conic Gradient: rotating ring masked strictly inside rounded-[1.75rem] -->
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-indigo-500/60 via-sky-400/40 to-indigo-500/60 opacity-60"></div>

        <!-- Inner Login Card -->
        <div class="login-card relative rounded-[1.65rem] border border-white/10 bg-slate-900/95 p-8 sm:p-10 transition-all duration-300"
             x-data="{
                showPassword: false,
                email: '{{ old('email', '') }}',
                password: '',
                fillDemo(role) {
                    if (role === 'admin') {
                        this.email = 'quan.nm@hrm.local';
                        this.password = 'Password123!';
                    } else if (role === 'hr') {
                        this.email = 'anh.tn@hrm.local';
                        this.password = 'Password123!';
                    } else if (role === 'employee') {
                        this.email = 'huy.pq@hrm.local';
                        this.password = 'Password123!';
                    }
                }
             }">

            <!-- Logo & Header -->
            <div class="mb-4 text-center">
                <div class="mx-auto flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-sky-400 text-white shadow-lg shadow-indigo-500/35 ring-2 ring-indigo-500/20">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h1 class="mt-2 text-xl font-extrabold tracking-tight text-white sm:text-2xl">Chào mừng bạn trở lại</h1>
                <p class="mt-0.5 text-sm text-slate-400">Đăng nhập vào hệ thống quản lý nhân sự.</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-3" :status="session('status')" />

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-500/20 bg-rose-500/10 p-3 text-xs text-rose-300" role="alert" aria-live="polite">
                    <div class="flex items-center gap-1.5 font-bold text-rose-400">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span>Không thể đăng nhập</span>
                    </div>
                    <p class="mt-0.5 pl-5">{{ $errors->first() }}</p>
                </div>
            @endif

            <!-- Quick Demo Login Fill Buttons: HR & Employee (Admin Separated) -->
            <div class="mb-3.5 rounded-xl border border-white/10 bg-white/5 p-2.5">
                <div class="flex items-center justify-between mb-2 px-0.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Thử nghiệm nhanh (1-Click Fill)</p>
                    <button type="button" @click="fillDemo('admin')" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-400 hover:text-indigo-300 transition hover:underline">
                        <span>Điền quyền Admin</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="fillDemo('hr')" class="flex items-center justify-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/15 py-2 px-2 text-center transition hover:bg-sky-500/25 active:scale-95">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-500/20 text-sky-300">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <div class="text-left">
                            <span class="block text-xs font-bold text-sky-300 leading-tight">Nhân sự</span>
                            <span class="block text-[9px] text-slate-400 leading-tight">HR Manager</span>
                        </div>
                    </button>
                    <button type="button" @click="fillDemo('employee')" class="flex items-center justify-center gap-2 rounded-xl border border-emerald-500/30 bg-emerald-500/15 py-2 px-2 text-center transition hover:bg-emerald-500/25 active:scale-95">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-500/20 text-emerald-300">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <div class="text-left">
                            <span class="block text-xs font-bold text-emerald-300 leading-tight">Nhân viên</span>
                            <span class="block text-[9px] text-slate-400 leading-tight">Employee</span>
                        </div>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300 mb-1">Email tài khoản</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <input id="email" x-model="email" type="email" name="email" required autofocus autocomplete="username" placeholder="name@company.com" class="block w-full rounded-xl border border-white/15 bg-slate-950/60 py-3 pl-10 pr-3 text-sm text-white placeholder-slate-500 shadow-inner transition focus:border-indigo-400 focus:bg-slate-950/90 focus:outline-none focus:ring-2 focus:ring-indigo-400/20">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">Mật khẩu</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-indigo-400 hover:text-indigo-300">Quên mật khẩu?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <input id="password" x-model="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="block w-full rounded-xl border border-white/15 bg-slate-950/60 py-3 pl-10 pr-10 text-sm text-white placeholder-slate-500 shadow-inner transition focus:border-indigo-400 focus:bg-slate-950/90 focus:outline-none focus:ring-2 focus:ring-indigo-400/20">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-white" :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'">
                            <svg x-show="!showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showPassword" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Ghi nhớ đăng nhập -->
                <div class="flex items-center pt-0.5">
                    <input id="remember_me" type="checkbox" name="remember" class="h-3.5 w-3.5 rounded border-white/20 bg-slate-950 text-indigo-600 focus:ring-indigo-500/40">
                    <label for="remember_me" class="ml-2 text-xs font-medium text-slate-300">Ghi nhớ đăng nhập</label>
                </div>

                <!-- Submit Button (Clean, No Arrow) -->
                <button type="submit" class="relative flex w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-600 to-sky-500 py-3.5 px-4 text-sm font-bold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110 hover:shadow-indigo-500/40 active:scale-[0.99] mt-2">
                    <span>Đăng nhập hệ thống</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Styles cho hiệu ứng viền xoay và cánh hoa nở đồng bộ với màu sắc mới -->
    <style>
        /* Viền xoay chuyển sắc phía sau Card đăng nhập */
        @keyframes login-border-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Container chứa các cánh hoa nở */
        .login-bloom-container {
            display: none !important;
            position: fixed;
            top: 0; left: 0;
            width: 100vw; height: 100vh;
            z-index: 99999;
            pointer-events: none;
            overflow: hidden;
        }

        .login-bloom-petal {
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 60% 40% 55% 45% / 50% 55% 45% 50%;
            opacity: 0;
            transform-origin: center center;
            filter: blur(1.5px);
        }

        /* Bảng màu giao diện HR Management */
        .login-bloom-petal:nth-child(1) { background: linear-gradient(135deg, #4f46e5, #6366f1); }
        .login-bloom-petal:nth-child(2) { background: linear-gradient(135deg, #0284c7, #38bdf8); }
        .login-bloom-petal:nth-child(3) { background: linear-gradient(135deg, #7c3aed, #a855f7); }
        .login-bloom-petal:nth-child(4) { background: linear-gradient(135deg, #059669, #10b981); }
        .login-bloom-petal:nth-child(5) { background: linear-gradient(135deg, #4338ca, #60a5fa); }
        .login-bloom-petal:nth-child(6) { background: linear-gradient(135deg, #6d28d9, #818cf8); }

        /* Nhụy hoa ở giữa với quầng sáng tỏa đa tầng */
        .login-bloom-center {
            position: absolute;
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background: radial-gradient(circle, #e0e7ff 0%, #818cf8 45%, #4f46e5 100%);
            opacity: 0;
            transform: translate(-50%, -50%) scale(0);
            box-shadow: 0 0 70px rgba(99, 102, 241, 0.9), 0 0 140px rgba(56, 189, 248, 0.6);
        }

        /* Keyframes bung cánh hoa theo 6 hướng */
        @keyframes petal-bloom-1 {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0) rotate(0deg); }
            30%  { opacity: 0.95; }
            100% { opacity: 0.95; transform: translate(calc(-50% + 0px), calc(-50% - 55vh)) scale(4.5) rotate(15deg); }
        }
        @keyframes petal-bloom-2 {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0) rotate(0deg); }
            30%  { opacity: 0.95; }
            100% { opacity: 0.95; transform: translate(calc(-50% + 48vw), calc(-50% - 28vh)) scale(4.5) rotate(-10deg); }
        }
        @keyframes petal-bloom-3 {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0) rotate(0deg); }
            30%  { opacity: 0.95; }
            100% { opacity: 0.95; transform: translate(calc(-50% + 48vw), calc(-50% + 28vh)) scale(4.5) rotate(20deg); }
        }
        @keyframes petal-bloom-4 {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0) rotate(0deg); }
            30%  { opacity: 0.95; }
            100% { opacity: 0.95; transform: translate(calc(-50% + 0px), calc(-50% + 55vh)) scale(4.5) rotate(-15deg); }
        }
        @keyframes petal-bloom-5 {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0) rotate(0deg); }
            30%  { opacity: 0.95; }
            100% { opacity: 0.95; transform: translate(calc(-50% - 48vw), calc(-50% + 28vh)) scale(4.5) rotate(10deg); }
        }
        @keyframes petal-bloom-6 {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0) rotate(0deg); }
            30%  { opacity: 0.95; }
            100% { opacity: 0.95; transform: translate(calc(-50% - 48vw), calc(-50% - 28vh)) scale(4.5) rotate(-20deg); }
        }

        @keyframes center-bloom {
            0%   { opacity: 0; transform: translate(-50%, -50%) scale(0); }
            40%  { opacity: 1; transform: translate(-50%, -50%) scale(1.2); }
            100% { opacity: 1; transform: translate(-50%, -50%) scale(3.2); }
        }

        .login-bloom-petal:nth-child(1) { animation: petal-bloom-1 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.00s forwards; }
        .login-bloom-petal:nth-child(2) { animation: petal-bloom-2 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.06s forwards; }
        .login-bloom-petal:nth-child(3) { animation: petal-bloom-3 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.12s forwards; }
        .login-bloom-petal:nth-child(4) { animation: petal-bloom-4 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.18s forwards; }
        .login-bloom-petal:nth-child(5) { animation: petal-bloom-5 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.09s forwards; }
        .login-bloom-petal:nth-child(6) { animation: petal-bloom-6 1.1s cubic-bezier(0.22, 1, 0.36, 1) 0.15s forwards; }
        .login-bloom-center { animation: center-bloom 0.95s cubic-bezier(0.22, 1, 0.36, 1) 0.00s forwards; }
    </style>

    <!-- Script kích hoạt hiệu ứng hoa nở và phai mờ mượt mà khi submit -->
    <script>
        (function() {
            const form = document.querySelector('form');
            const submitBtn = form?.querySelector('button[type="submit"]') || form?.querySelector('button');

            if (form && submitBtn) {
                form.addEventListener('submit', function(e) {
                    // Submit immediately; keep the login flow clear and responsive.
                    return;
                    if (!form.checkValidity()) return;

                    e.preventDefault();

                    // Lấy tọa độ tâm của card đăng nhập
                    const cardWrapper = document.querySelector('.login-card-container') || document.querySelector('.login-card') || form;
                    const rect = cardWrapper.getBoundingClientRect();
                    const cx = rect.left + rect.width / 2;
                    const cy = rect.top + rect.height / 2;

                    // Tạo container chứa hiệu ứng hoa nở
                    const container = document.createElement('div');
                    container.className = 'login-bloom-container';

                    // Tạo 6 cánh hoa bung tỏa
                    for (let i = 0; i < 6; i++) {
                        const petal = document.createElement('div');
                        petal.className = 'login-bloom-petal';
                        petal.style.left = cx + 'px';
                        petal.style.top = cy + 'px';
                        container.appendChild(petal);
                    }

                    // Tạo nhụy hoa ở giữa
                    const center = document.createElement('div');
                    center.className = 'login-bloom-center';
                    center.style.left = cx + 'px';
                    center.style.top = cy + 'px';
                    container.appendChild(center);

                    document.body.appendChild(container);

                    // Làm mờ card đăng nhập nhẹ nhàng
                    setTimeout(() => {
                        const card = document.querySelector('.login-card') || cardWrapper;
                        if (card) {
                            card.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.96)';
                        }
                    }, 300);

                    // Làm mờ toàn bộ nền trang để chuyển hướng mượt mà
                    setTimeout(() => {
                        document.body.style.transition = 'opacity 0.4s ease';
                        document.body.style.opacity = '0';
                    }, 750);

                    // Submit form thực tế sau khi hiệu ứng hoa nở hoàn tất
                    setTimeout(() => {
                        form.submit();
                    }, 1050);
                });
            }
        })();
    </script>
</x-guest-layout>
