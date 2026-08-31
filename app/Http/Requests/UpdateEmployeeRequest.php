<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
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
        $employee = $this->route('employee');
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee?->user_id)],
            'department_id' => ['required', 'exists:departments,id'],
            'employee_code' => ['required', 'string', 'max:30', Rule::unique('employees', 'employee_code')->ignore($employee)],
            'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'], 'position' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['required', 'date'], 'employment_status' => ['required', 'in:active,inactive'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
