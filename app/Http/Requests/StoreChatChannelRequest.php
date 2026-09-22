<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreChatChannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isHr());
    }

    protected function prepareForValidation(): void
    {
        if (empty($this->slug) && ! empty($this->name)) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:120', 'unique:chat_channels,slug', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:department,group'],
            'department_id' => ['nullable', 'required_if:type,department', 'exists:departments,id'],
            'members' => ['nullable', 'array'],
            'members.*' => ['exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên kênh.',
            'name.max' => 'Tên kênh không được vượt quá 100 ký tự.',
            'slug.required' => 'Mã định danh slug là bắt buộc.',
            'slug.unique' => 'Mã định danh slug này đã tồn tại.',
            'slug.regex' => 'Slug chỉ bao gồm chữ thường không dấu, số và dấu gạch ngang.',
            'description.max' => 'Mô tả không được vượt quá 255 ký tự.',
            'type.required' => 'Vui lòng chọn loại kênh.',
            'type.in' => 'Loại kênh không hợp lệ.',
            'department_id.required_if' => 'Vui lòng chọn phòng ban liên kết cho kênh phòng ban.',
            'department_id.exists' => 'Phòng ban được chọn không tồn tại.',
            'members.array' => 'Danh sách thành viên không đúng định dạng.',
            'members.*.exists' => 'Thành viên được chọn không tồn tại.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->type === 'department' && ! empty($this->department_id) && ! empty($this->members)) {
                $memberIds = array_unique(array_filter((array) $this->members));
                if (! empty($memberIds)) {
                    $validCount = \App\Models\Employee::whereIn('user_id', $memberIds)
                        ->where('department_id', $this->department_id)
                        ->count();

                    if ($validCount !== count($memberIds)) {
                        $validator->errors()->add('members', 'Chỉ được thêm nhân viên thuộc phòng ban này vào kênh phòng ban.');
                    }
                }
            }
        });
    }
}
