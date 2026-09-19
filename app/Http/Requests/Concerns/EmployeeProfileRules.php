<?php

namespace App\Http\Requests\Concerns;

trait EmployeeProfileRules
{
    protected function employeeProfileRules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'hire_date' => ['required', 'date'],
        ];
    }
}
