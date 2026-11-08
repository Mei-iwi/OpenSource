<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;

trait ContractLegalRules
{
    protected function contractLegalRules(): array
    {
        return [
            'contract_type' => ['nullable', Rule::in(['probation', 'fixed-term', 'indefinite'])],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', Rule::when($this->filled('contract_start_date'), ['after_or_equal:contract_start_date']), Rule::prohibitedIf($this->input('contract_type') === 'indefinite')],
            'identity_number' => ['nullable', 'string', 'max:20'],
            'tax_code' => ['nullable', 'string', 'max:30'],
            'social_insurance_number' => ['nullable', 'string', 'max:30'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
        ];
    }
}
