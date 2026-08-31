<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Attendance;
use Illuminate\Validation\Rule;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(['admin', 'hr']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $attendance = $this->route('attendance');
        return [
            'employee_id' => ['required', 'integer', 'exists:employees,id', Rule::unique('attendances')->ignore($attendance instanceof Attendance ? $attendance->id : $attendance)->where(fn ($query) => $query->where('work_date', $this->input('work_date')))],
            'work_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after_or_equal:check_in'],
            'status' => ['required', Rule::in(['present', 'late', 'absent', 'leave'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
