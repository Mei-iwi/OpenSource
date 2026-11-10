<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SelfAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $employee = $this->user()?->employee;

        return $employee !== null && $employee->employment_status === 'active';
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:3072'],
            'method' => ['required', Rule::in(['camera'])],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['required', 'numeric', 'min:0', 'max:10000'],
        ];
    }
}
