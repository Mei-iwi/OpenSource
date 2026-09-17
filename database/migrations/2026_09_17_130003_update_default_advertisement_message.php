<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const OLD_MESSAGE = 'Sự nỗ lực của bạn là thu nhập của tôi. Cảm ơn bạn đã tiếp tục cống hiến!';

    private const NEW_MESSAGE = 'Nhờ tinh thần cống hiến không ngừng của đội ngũ nhân viên, doanh thu công ty vẫn duy trì đà tăng trưởng ấn tượng theo hướng âm. Để cải thiện tình hình, công ty đã nhanh chóng triển khai chiến dịch quảng cáo sản phẩm đến chính nhân viên, biến người lao động từ lực lượng tạo ra doanh thu thành lực lượng trực tiếp đóng góp doanh thu bằng cách mua sản phẩm của công ty mình.';

    public function up(): void
    {
        DB::table('advertisements')
            ->where('message', self::OLD_MESSAGE)
            ->update(['message' => self::NEW_MESSAGE, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('advertisements')
            ->where('message', self::NEW_MESSAGE)
            ->update(['message' => self::OLD_MESSAGE, 'updated_at' => now()]);
    }
};
