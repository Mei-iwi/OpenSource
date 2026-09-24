<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    /**
     * Determine whether the user can view any attendances.
     */
    public function viewAny(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can view the attendance.
     */
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']) || ($user->employee && $user->employee->id === $attendance->employee_id);
    }

    /**
     * Determine whether the user can create attendances.
     */
    public function create(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can update the attendance.
     */
    public function update(User $user, Attendance $attendance): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can delete the attendance.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }
}
