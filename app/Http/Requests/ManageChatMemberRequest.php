<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageChatMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $channel = $this->route('channel');

        return $this->user() && $this->user()->can('manageMembers', $channel);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Vui lòng chọn ít nhất một nhân sự để thêm vào kênh.',
            'user_ids.array' => 'Danh sách nhân sự không hợp lệ.',
            'user_ids.min' => 'Vui lòng chọn ít nhất một nhân sự để thêm vào kênh.',
            'user_ids.*.exists' => 'Nhân sự được chọn không tồn tại trong hệ thống.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $channel = $this->route('channel');
            if ($channel && $channel->type === 'department' && ! empty($channel->department_id) && ! empty($this->user_ids)) {
                $userIds = array_unique(array_filter((array) $this->user_ids));
                if (! empty($userIds)) {
                    $validCount = \App\Models\Employee::whereIn('user_id', $userIds)
                        ->where('department_id', $channel->department_id)
                        ->count();

                    if ($validCount !== count($userIds)) {
                        $validator->errors()->add('user_ids', 'Chỉ được thêm nhân viên thuộc đúng phòng ban của kênh này.');
                    }
                }
            }
        });
    }
}
