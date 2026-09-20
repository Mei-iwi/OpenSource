<?php

namespace App\Providers;

use App\Models\Advertisement;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function ($user, string $token) {
            return (new MailMessage)
                ->subject('Đặt lại mật khẩu — '.config('app.name'))
                ->greeting('Xin chào '.$user->name.',')
                ->line('Hệ thống nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.')
                ->action('Xác thực và đặt mật khẩu mới', route('password.reset', ['token' => $token, 'email' => $user->email]))
                ->line('Liên kết có hiệu lực trong '.config('auth.passwords.users.expire').' phút và chỉ dùng được một lần.')
                ->line('Nếu bạn không yêu cầu thay đổi, hãy bỏ qua email này. Mật khẩu hiện tại vẫn giữ nguyên.')
                ->salutation(config('app.name'));
        });

        View::composer('layouts.app', function ($view): void {
            $user = auth()->user();
            $view->with('activeAdvertisement', $user && ! $user->isAdmin()
                ? Advertisement::active()->latest('id')->first()
                : null);
        });
    }
}
