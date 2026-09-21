<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Standard Working Schedule & Late Detection Configuration
    |--------------------------------------------------------------------------
    |
    | - work_start_time: Standard work shift start time (HH:MM format, 24-hour).
    | - grace_period_minutes: Minutes allowed after work_start_time before
    |   marking check-in as late.
    |   Default: 08:00 start with 15 mins grace -> 08:15 is on time (present),
    |   after 08:15 (e.g. 08:15:01 / 08:16:00) is late.
    | - timezone: Timezone used to evaluate check-in timestamps.
    | - allow_weekend_checkin: Whether employees can check in on Saturdays & Sundays.
    |
    */

    'work_start_time' => env('ATTENDANCE_WORK_START_TIME', '08:00'),

    'grace_period_minutes' => (int) env('ATTENDANCE_GRACE_PERIOD_MINUTES', 15),

    'timezone' => env('ATTENDANCE_TIMEZONE', config('app.timezone', 'Asia/Ho_Chi_Minh')),

    'allow_weekend_checkin' => (bool) env('ATTENDANCE_ALLOW_WEEKEND_CHECKIN', true),

    'work_days' => [1, 2, 3, 4, 5], // 1 = Monday ... 5 = Friday

];
