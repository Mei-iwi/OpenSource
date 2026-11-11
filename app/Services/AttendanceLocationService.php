<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class AttendanceLocationService
{
    public function distanceMeters(float $latitude, float $longitude, float $accuracy): int
    {
        if ($accuracy > (float) config('attendance.max_location_accuracy_meters')) {
            throw ValidationException::withMessages(['location' => 'Vị trí chưa đủ chính xác. Hãy bật GPS và thử lại.']);
        }

        $siteLatitude = deg2rad((float) config('attendance.site_latitude'));
        $latitudeRad = deg2rad($latitude);
        $deltaLatitude = $latitudeRad - $siteLatitude;
        $deltaLongitude = deg2rad($longitude - (float) config('attendance.site_longitude'));
        $haversine = sin($deltaLatitude / 2) ** 2
            + cos($siteLatitude) * cos($latitudeRad) * sin($deltaLongitude / 2) ** 2;
        $distance = 6371000 * 2 * asin(min(1, sqrt($haversine)));

        if ($distance > (float) config('attendance.site_radius_meters')) {
            throw ValidationException::withMessages(['location' => 'Bạn cần ở gần địa điểm làm việc để chấm công.']);
        }

        return (int) round($distance);
    }
}
