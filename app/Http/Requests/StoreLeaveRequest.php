<?php

namespace App\Http\Requests;

use App\Services\AnnualLeaveService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['admin', 'hr', 'employee']) && $user->employee !== null;
    }

    public function rules(): array
    {
        return ['leave_type' => ['required', Rule::in(['annual', 'sick', 'unpaid', 'other'])], 'start_date' => ['required', 'date', 'before_or_equal:end_date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'reason' => ['required', 'string', 'max:2000']];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $employee = $this->user()?->employee;
            if ($validator->errors()->isNotEmpty() || ! $employee) {
                return;
            }
            $overlap = $employee->leaveRequests()->whereIn('status', ['pending', 'approved'])->where('start_date', '<=', $this->end_date)->where('end_date', '>=', $this->start_date)->exists();
            if ($overlap) {
                $validator->errors()->add('start_date', 'Khoảng thời gian nghỉ bị trùng với đơn đang chờ hoặc đã duyệt.');
                return;
            }

            if ($this->leave_type === 'annual') {
                try {
                    app(AnnualLeaveService::class)->validateAndReserveQuota(
                        $employee,
                        $this->start_date,
                        $this->end_date
                    );
                } catch (ValidationException $e) {
                    foreach ($e->errors() as $key => $messages) {
                        foreach ($messages as $msg) {
                            $validator->errors()->add($key, $msg);
                        }
                    }
                }
            }
        });
    }
}
