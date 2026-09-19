<?php

namespace App\Actions;

use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendPasswordResetLink
{
    public function __invoke(string $email): string
    {
        try {
            $status = Password::sendResetLink(['email' => $email]);
        } catch (TransportExceptionInterface $exception) {
            // Transport errors can include SMTP credentials; never expose them.
            throw ValidationException::withMessages(['email' => 'Không gửi được email. Vui lòng kiểm tra cấu hình SMTP hoặc thử lại sau.']);
        }

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return 'Đã gửi liên kết đặt lại mật khẩu. Vui lòng kiểm tra hộp thư và mục thư rác.';
    }
}
