<x-guest-layout>
    <div class="mb-8 text-center">
        <span class="inline-flex items-center rounded-full bg-white/20 px-4 py-1.5 text-xs font-bold tracking-[0.18em] text-white ring-1 ring-white/30">QUẢN LÝ NHÂN SỰ</span>
        <h1 class="mt-5 text-3xl font-bold tracking-tight text-white sm:text-4xl">Chào mừng bạn trở lại</h1>
        <p class="mt-3 text-sm text-blue-50">Đăng nhập để tiếp tục quản lý công việc của bạn.</p>
    </div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200/80 bg-red-50/95 p-4 text-sm text-red-700" role="alert" aria-live="polite">
            <p class="font-semibold">Không thể đăng nhập</p>
            <p class="mt-1">{{ $errors->first() }}</p>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" class="text-white" />
            <x-text-input id="email" class="mt-2 block w-full rounded-xl border-white/50 bg-white/95 px-4 py-3 text-slate-900 shadow-sm focus:border-cyan-200 focus:ring-cyan-200" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" aria-describedby="email-error" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Mật khẩu" class="text-white" />

            <div class="relative mt-2">
                <x-text-input id="password" class="block w-full rounded-xl border-white/50 bg-white/95 px-4 py-3 pe-12 text-slate-900 shadow-sm focus:border-cyan-200 focus:ring-cyan-200" x-bind:type="showPassword ? 'text' : 'password'" name="password" required autocomplete="current-password" aria-describedby="password-error" />
                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center px-4 text-sm font-semibold text-slate-500 hover:text-blue-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan-300" :aria-label="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'" :title="showPassword ? 'Ẩn mật khẩu' : 'Hiện mật khẩu'">
                    <span x-text="showPassword ? 'Ẩn' : 'Hiện'"></span>
                </button>
            </div>

            <x-input-error id="password-error" :messages="$errors->get('password')" class="mt-2 text-red-100" />
        </div>

        <!-- Ghi nhớ đăng nhập -->
        <div class="mt-5 block">
            <label for="remember_me" class="inline-flex items-center text-sm text-white">
                <input id="remember_me" type="checkbox" class="rounded border-white/60 text-blue-700 shadow-sm focus:ring-cyan-300" name="remember">
                <span class="ms-2">Ghi nhớ đăng nhập</span>
            </label>
        </div>

        <div class="mt-6 flex flex-col-reverse items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="rounded-md text-sm font-medium text-blue-50 underline decoration-blue-200 underline-offset-4 hover:text-white focus:outline-none focus:ring-2 focus:ring-cyan-300" href="{{ route('password.request') }}">
                    Quên mật khẩu?
                </a>
            @endif

            <x-primary-button class="justify-center rounded-xl bg-slate-950 px-5 py-3 text-sm text-white shadow-lg shadow-blue-950/20 hover:bg-blue-950 focus:bg-blue-950 active:bg-slate-900 focus:ring-cyan-300">
                Đăng nhập
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
