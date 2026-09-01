<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isEmployee() ?? false; }

    public function rules(): array
    {
        return ['leave_type' => ['required', Rule::in(['annual', 'sick', 'unpaid', 'other'])], 'start_date' => ['required', 'date', 'before_or_equal:end_date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'reason' => ['required', 'string', 'max:2000']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $employee = $this->user()?->employee;
            if (! $employee || ! $this->start_date || ! $this->end_date) return;
            $overlap = $employee->leaveRequests()->whereIn('status', ['pending', 'approved'])->where('start_date', '<=', $this->end_date)->where('end_date', '>=', $this->start_date)->exists();
            if ($overlap) $validator->errors()->add('start_date', 'Khoảng thời gian nghỉ bị trùng với đơn đang chờ hoặc đã duyệt.');
        });
    }
}
