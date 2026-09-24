<?php

namespace App\Policies;

use App\Models\JobPosition;
use App\Models\User;

class JobPositionPolicy
{
    /**
     * Determine whether the user can view any job positions.
     */
    public function viewAny(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can view the job position.
     */
    public function view(User $user, JobPosition $jobPosition): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can create job positions.
     */
    public function create(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can update the job position.
     */
    public function update(User $user, JobPosition $jobPosition): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can delete the job position.
     */
    public function delete(User $user, JobPosition $jobPosition): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }
}
