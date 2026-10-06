<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Annual Leave Quota
    |--------------------------------------------------------------------------
    |
    | Số ngày nghỉ phép năm tiêu chuẩn mặc định cho mỗi nhân viên mỗi năm.
    | Có thể cấu hình qua biến môi trường LEAVE_DEFAULT_ANNUAL_DAYS.
    |
    */
    'default_annual_days' => (int) env('LEAVE_DEFAULT_ANNUAL_DAYS', 12),

    /*
    |--------------------------------------------------------------------------
    | Prorate First Year Leave by Join Date
    |--------------------------------------------------------------------------
    |
    | Nếu nhân viên bắt đầu làm việc giữa năm, tự động tính tỷ lệ số ngày phép
    | theo số tháng làm việc thực tế trong năm đầu tiên.
    |
    */
    'prorate_join_year' => (bool) env('LEAVE_PRORATE_JOIN_YEAR', true),

    /*
    |--------------------------------------------------------------------------
    | Allow Negative Balance
    |--------------------------------------------------------------------------
    |
    | Cho phép hoặc chặn tuyệt đối việc nghỉ vượt quá số ngày phép còn lại.
    |
    */
    'allow_negative_balance' => false,
];
