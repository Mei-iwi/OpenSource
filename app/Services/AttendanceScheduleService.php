<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use DateTimeInterface;

class AttendanceScheduleService
{
    /**
     * Determine attendance status ('present' or 'late') based on configured schedule.
     */
    public function determineStatus(CarbonInterface|DateTimeInterface|string $checkInTime, ?CarbonInterface $date = null): string
    {
        $timezone = $this->getTimezone();

        $carbonTime = match (true) {
            $checkInTime instanceof CarbonInterface => $checkInTime->copy()->setTimezone($timezone),
            $checkInTime instanceof DateTimeInterface => Carbon::instance($checkInTime)->setTimezone($timezone),
            default => $date
                ? Carbon::parse($date->toDateString().' '.$checkInTime, $timezone)
                : Carbon::parse($checkInTime, $timezone),
        };

        $graceBoundary = $this->getGraceBoundaryTime($carbonTime);

        return $carbonTime->lessThanOrEqualTo($graceBoundary) ? 'present' : 'late';
    }

    /**
     * Check if a given check-in time is considered late.
     */
    public function isLate(CarbonInterface|DateTimeInterface|string $checkInTime): bool
    {
        return $this->determineStatus($checkInTime) === 'late';
    }

    /**
     * Calculate exact grace boundary timestamp for the given date.
     */
    public function getGraceBoundaryTime(CarbonInterface $date): CarbonInterface
    {
        $timezone = $this->getTimezone();
        $startTime = $this->getWorkStartTime();
        $graceMinutes = $this->getGracePeriodMinutes();

        return $date->copy()
            ->setTimezone($timezone)
            ->setTimeFromTimeString($startTime)
            ->addMinutes($graceMinutes);
    }

    /**
     * Get configured work start time (default '08:00').
     */
    public function getWorkStartTime(): string
    {
        return (string) config('attendance.work_start_time', '08:00');
    }

    /**
     * Get configured grace period in minutes (default 15).
     */
    public function getGracePeriodMinutes(): int
    {
        return (int) config('attendance.grace_period_minutes', 15);
    }

    /**
     * Get configured attendance timezone.
     */
    public function getTimezone(): string
    {
        return (string) config('attendance.timezone', config('app.timezone', 'Asia/Ho_Chi_Minh'));
    }

    /**
     * Check whether weekend check-in is permitted.
     */
    public function isWeekendCheckinAllowed(): bool
    {
        return (bool) config('attendance.allow_weekend_checkin', true);
    }

    /**
     * Verify if check-in is allowed on the specified date/time.
     */
    public function isCheckinAllowed(CarbonInterface $time): bool
    {
        if ($time->isWeekend() && ! $this->isWeekendCheckinAllowed()) {
            return false;
        }

        return true;
    }

    /**
     * Return schedule summary for UI / reports.
     */
    public function getScheduleSummary(?CarbonInterface $referenceDate = null): array
    {
        $now = $referenceDate ?: Carbon::now($this->getTimezone());
        $boundary = $this->getGraceBoundaryTime($now);

        return [
            'work_start_time' => $this->getWorkStartTime(),
            'grace_period_minutes' => $this->getGracePeriodMinutes(),
            'grace_boundary_time' => $boundary->format('H:i'),
            'timezone' => $this->getTimezone(),
            'allow_weekend_checkin' => $this->isWeekendCheckinAllowed(),
        ];
    }
}
