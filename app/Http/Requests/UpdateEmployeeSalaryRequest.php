<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeSalaryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'hr']) ?? false;
    }

    public function rules(): array
    {
        $amount = ['required', 'regex:/^\d{1,12}(?:\.\d{1,2})?$/'];

        return [
            'base_salary' => $amount,
            'allowance' => $amount,
            'fixed_deduction' => $amount,
        ];
    }
}
