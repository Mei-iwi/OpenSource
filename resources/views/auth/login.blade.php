<x-guest-layout>
    <div class="login-card-container relative z-10 w-full overflow-hidden rounded-[1.75rem] p-[2px] shadow-xl shadow-indigo-200/60 dark:shadow-indigo-950/50">
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-indigo-400/50 via-sky-300/60 to-emerald-300/50 dark:from-indigo-500/60 dark:via-sky-400/40 dark:to-indigo-500/60"></div>

        <div class="login-card relative rounded-[1.65rem] border border-white/80 bg-white/95 p-8 text-slate-800 transition-all duration-300 dark:border-white/10 dark:bg-slate-900/95 dark:text-slate-100 sm:p-10"
             x-data="{
                showPassword: false,
                email: '{{ old('email', '') }}',
                password: '',
                fillDemo(role) {
                    if (role === 'admin') {
                        this.email = 'quan.nm@admin.hr-management.com';
                        this.password = 'Password123!';
                    } else if (role === 'hr') {
                        this.email = 'anh.tn@hr.hr-management.com';
                        this.password = 'Password123!';
                    } else if (role === 'employee') {
                        this.email = 'huy.pq@emp.hr-management.com';
                        this.password = 'Password123!';
                    }
                }
             }">

            <div class="mb-4 text-center">
                <div class="snake-avatar-motion mx-auto flex h-11 w-11 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-tr from-emerald-700 via-emerald-600 to-lime-500 text-white shadow-lg shadow-emerald-900/35 ring-2 ring-emerald-500/20" aria-label="Ảnh rắn chuyển động">
                    <img src="{{ asset('images/default-employee.png') }}" alt="Ảnh rắn chuyển động" class="h-full w-full object-cover">
                </div>
                <h1 class="mt-2 text-xl font-extrabold tracking-tight text-slate-950 dark:text-white sm:text-2xl">Chào mừng bạn trở lại</h1>
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Đăng nhập vào hệ thống quản lý nhân sự.</p>
            </div>

            <x-auth-session-status class="mb-3" :status="session('status')" />

            @if ($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300" role="alert" aria-live="polite">
                    <div class="flex items-center gap-1.5 font-bold text-rose-600 dark:text-rose-400">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span>Không thể đăng nhập</span>
                    </div>
                    <p class="mt-0.5 pl-5">{{ $errors->first() }}</p>
                </div>
            @endif

            <div class="mb-3.5 rounded-xl border border-slate-200 bg-slate-50 p-2.5 dark:border-white/10 dark:bg-white/5">
                <div class="flex items-center justify-between mb-2 px-0.5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Thử nghiệm nhanh (1-Click Fill)</p>
                    <button type="button" @click="fillDemo('admin')" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 transition hover:text-indigo-800 hover:underline dark:text-indigo-400 dark:hover:text-indigo-300">
                        <span>Điền quyền Admin</span>
                    </button>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="fillDemo('hr')" class="flex items-center justify-center gap-2 rounded-xl border border-sky-200 bg-sky-50 px-2 py-2 text-center transition hover:bg-sky-100 active:scale-95 dark:border-sky-500/30 dark:bg-sky-500/15 dark:hover:bg-sky-500/25">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <div class="text-left">
                            <span class="block text-xs font-bold leading-tight text-sky-700 dark:text-sky-300">Nhân sự</span>
                            <span class="block text-[9px] leading-tight text-slate-500 dark:text-slate-400">HR Manager</span>
                        </div>
                    </button>
                    <button type="button" @click="fillDemo('employee')" class="flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-2 text-center transition hover:bg-emerald-100 active:scale-95 dark:border-emerald-500/30 dark:bg-emerald-500/15 dark:hover:bg-emerald-500/25">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <div class="text-left">
                            <span class="block text-xs font-bold leading-tight text-emerald-700 dark:text-emerald-300">Nhân viên</span>
                            <span class="block text-[9px] leading-tight text-slate-500 dark:text-slate-400">Employee</span>
                        </div>
                    </button>
                </div>
            </div>

            <form id="login-form" method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Email tài khoản</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        </div>
                        <input id="email" x-model="email" type="email" name="email" required autofocus autocomplete="username" placeholder="name@company.com" class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-10 pr-3 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 dark:border-white/15 dark:bg-slate-950/60 dark:text-white dark:placeholder-slate-500 dark:focus:bg-slate-950/90">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Mật khẩu</label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300">Quên mật khẩu?</a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <input id="password" x-model="password" :type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" placeholder="••••••••" class="block w-full rounded-xl border border-slate-300 bg-white py-3 pl-10 pr-10 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/20 dark:border-white/15 dark:bg-slate-950/60 dark:text-white dark:placeholder-slate-500 dark:focus:bg-slate-950/90">
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-700 dark:hover:text-white" :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'">
                            <svg x-show="!showPassword" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="showPassword" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center pt-0.5">
                    <input id="remember_me" type="checkbox" name="remember" class="h-3.5 w-3.5 rounded border-slate-300 bg-white text-indigo-600 focus:ring-indigo-500/40 dark:border-white/20 dark:bg-slate-950">
                    <label for="remember_me" class="ml-2 text-xs font-medium text-slate-600 dark:text-slate-300">Ghi nhớ đăng nhập</label>
                </div>

                <button id="login-submit" type="submit" class="relative mt-2 flex w-full items-center justify-center overflow-hidden rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-600 to-sky-500 px-4 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110 hover:shadow-indigo-500/40 active:scale-[0.99] disabled:cursor-wait disabled:opacity-80">
                    <span id="login-submit-label">Đăng nhập hệ thống</span>
                </button>
            </form>
        </div>
    </div>

    <div id="login-snake-transition" class="login-snake-transition" aria-hidden="true">
        <div class="login-snake-runner">
            <img src="{{ asset('images/default-employee.png') }}" alt="">
        </div>
        <p>Đang mở bàn làm việc...</p>
    </div>

    <script>
        let loginIsSubmitting = false;

        document.getElementById('login-form')?.addEventListener('submit', function (event) {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            event.preventDefault();
            if (loginIsSubmitting) {
                return;
            }
            loginIsSubmitting = true;

            const submitButton = document.getElementById('login-submit');
            const submitLabel = document.getElementById('login-submit-label');
            const transition = document.getElementById('login-snake-transition');

            submitButton.disabled = true;
            submitLabel.textContent = 'Đang đăng nhập...';
            transition.classList.add('is-active');

            window.setTimeout(() => this.submit(), 650);
        });
    </script>
</x-guest-layout>
