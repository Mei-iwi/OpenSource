<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $channel = $this->route('channel');

        return $this->user() && $this->user()->can('sendMessage', $channel);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->message)) {
            $this->merge([
                'message' => trim($this->message),
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
            'message' => ['nullable', 'string', 'max:3000', 'required_without:attachments'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,txt,zip'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required_without' => 'Vui lòng nhập nội dung hoặc đính kèm tệp tin.',
            'message.max' => 'Tin nhắn không được vượt quá 3.000 ký tự.',
            'attachments.max' => 'Bạn chỉ được đính kèm tối đa 5 tệp mỗi tin nhắn.',
            'attachments.*.max' => 'Mỗi tệp đính kèm không được vượt quá 10MB.',
            'attachments.*.mimes' => 'Định dạng tệp không được hỗ trợ.',
        ];
    }
}
