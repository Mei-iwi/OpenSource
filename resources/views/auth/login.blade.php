<x-guest-layout>
    <div class="mb-8 text-center"><span class="inline-flex rounded-full bg-blue-100 px-4 py-1.5 text-xs font-bold tracking-wide text-blue-700">QUẢN LÝ NHÂN SỰ</span><h1 class="mt-5 text-3xl font-bold text-slate-900">Chào mừng bạn trở lại</h1><p class="mt-2 text-sm text-slate-500">Đăng nhập để tiếp tục quản lý công việc của bạn.</p></div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Mật khẩu" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Ghi nhớ đăng nhập -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ghi nhớ đăng nhập</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    Quên mật khẩu?
                </a>
            @endif

            <x-primary-button class="ms-3 bg-blue-600 text-white hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:ring-blue-400">
                Đăng nhập
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
