<section>
    <h2 class="app-heading">Đổi mật khẩu</h2>
    <p class="app-subtitle">Xác nhận mật khẩu hiện tại để nhận liên kết tại {{ $user->email }}. Mật khẩu chỉ thay đổi sau khi bạn mở liên kết trong email và đặt mật khẩu mới.</p>
    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf @method('PUT')
        <div>
            <label for="current_password" class="app-label">Mật khẩu hiện tại</label>
            <input id="current_password" name="current_password" type="password" class="app-input w-full" required autocomplete="current-password">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>
        <x-input-error :messages="$errors->get('email')" />
        <button type="submit" class="app-button-primary">Gửi liên kết xác thực</button>
    </form>
</section>
