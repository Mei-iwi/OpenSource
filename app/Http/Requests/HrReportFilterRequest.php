<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HrReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'hr']) ?? false;
    }

    public function rules(): array
    {
        return [
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100', 'required_with:month'],
            'department_code' => ['nullable', 'string', 'max:30'],
            'employee_code' => ['nullable', 'string', 'max:30'],
            'movement_type' => ['nullable', Rule::in(['new_hire', 'inactive'])],
            'leave_type' => ['nullable', Rule::in(['annual', 'sick', 'unpaid', 'other'])],
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }
}
