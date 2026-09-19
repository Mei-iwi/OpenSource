<?php

namespace App\Http\Requests\Concerns;

trait EmployeeProfileRules
{
    protected function employeeProfileRules(): array
    {
        return [
            'employee_code' => ['required', 'string', 'max:30', 'unique:employees,employee_code'],
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'hire_date' => ['required', 'date'],
        ];
    }
}
