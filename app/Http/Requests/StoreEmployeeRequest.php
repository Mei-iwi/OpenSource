<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->user()?->hasRole(['admin', 'hr']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_code' => ['required', 'string', 'max:30', 'unique:employees,employee_code'],
            'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'], 'position' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['required', 'date'], 'employment_status' => ['required', 'in:active,inactive'],
        ];
    }
}
