<x-guest-layout>
    <div class="relative overflow-hidden rounded-[1.75rem] border border-white/80 bg-white/95 p-8 text-center shadow-xl shadow-indigo-200/60 dark:border-white/10 dark:bg-slate-900/95 dark:shadow-indigo-950/50 sm:p-10">
        <div class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-tr from-emerald-700 via-emerald-600 to-lime-500 shadow-lg shadow-emerald-900/25 ring-4 ring-emerald-500/10">
            <img src="{{ asset('images/default-employee.png') }}" alt="Biểu tượng công ty" class="h-full w-full object-cover">
        </div>

        <h1 class="mt-5 text-2xl font-extrabold tracking-tight text-slate-950 dark:text-white">Hẹn gặp lại bạn</h1>
        <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Bạn đã đăng xuất an toàn khỏi hệ thống.</p>

        <div class="mx-auto mt-7 flex h-20 w-20 items-center justify-center rounded-full border-4 border-indigo-100 bg-indigo-50 text-3xl font-extrabold tabular-nums text-indigo-600 shadow-inner dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300" aria-live="polite">
            <span data-goodbye-countdown>5</span>
        </div>
        <p class="mt-3 text-xs font-medium text-slate-500 dark:text-slate-400">Tự động trở về trang đăng nhập sau <span data-goodbye-countdown>5</span> giây</p>

        <a href="{{ route('login') }}" class="mt-7 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-indigo-500 via-indigo-600 to-sky-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-500/25 transition hover:brightness-110">
            Trở về trang đăng nhập ngay
        </a>
    </div>

    <script>
        (() => {
            const loginUrl = @js(route('login'));
            const countdownElements = document.querySelectorAll('[data-goodbye-countdown]');
            let seconds = 5;

            const countdownTimer = window.setInterval(() => {
                seconds = Math.max(0, seconds - 1);
                countdownElements.forEach((element) => {
                    element.textContent = seconds;
                });
            }, 1000);

            window.setTimeout(() => {
                window.clearInterval(countdownTimer);
                window.location.href = loginUrl;
            }, 5000);
        })();
    </script>
</x-guest-layout>
