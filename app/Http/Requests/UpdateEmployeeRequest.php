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
        $actor = $this->user();
        $target = $this->route('employee')?->user;

        // HR cannot change an administrator's recovery email via the employee form.
        return $actor?->isAdmin() || ($actor?->isHr() && ! $target?->isAdmin());
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
            'employee_code' => ['prohibited'],
            'role' => ['prohibited'],
            'phone' => ['nullable', 'string', 'max:50'], 'address' => ['nullable', 'string'],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:today'], 'position' => ['required', 'string', 'max:255', 'exists:job_positions,name'],
            'hire_date' => ['required', 'date'], 'employment_status' => ['required', 'in:active,inactive'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
