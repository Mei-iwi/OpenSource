<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChatMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $message = $this->route('message');

        return $this->user() && $this->user()->can('update', $message);
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
            'message' => ['required', 'string', 'min:1', 'max:3000'],
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Nội dung tin nhắn không được để trống.',
            'message.min' => 'Nội dung tin nhắn không được để trống.',
            'message.max' => 'Tin nhắn không được vượt quá 3.000 ký tự.',
        ];
    }
}
