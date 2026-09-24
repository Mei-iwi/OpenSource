<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

class DepartmentPolicy
{
    /**
     * Determine whether the user can view any departments.
     */
    public function viewAny(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can view the department.
     */
    public function view(User $user, Department $department): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can create departments.
     */
    public function create(User $user): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can update the department.
     */
    public function update(User $user, Department $department): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }

    /**
     * Determine whether the user can delete the department.
     */
    public function delete(User $user, Department $department): bool
    {
        if ($user->account_status !== 'active') {
            return false;
        }

        return $user->hasRole(['admin', 'hr']);
    }
}
