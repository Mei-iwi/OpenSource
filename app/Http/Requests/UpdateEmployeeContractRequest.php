<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ContractLegalRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeContractRequest extends FormRequest
{
    use ContractLegalRules;

    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->route('employee')?->user;

        return $actor?->isAdmin() || ($actor?->isHr() && ! $target?->isAdmin());
    }

    public function rules(): array
    {
        $rules = $this->contractLegalRules();
        $rules['contract_start_date'][] = 'required_with:contract_type';
        $rules['contract_end_date'][] = Rule::requiredIf(in_array($this->input('contract_type'), ['probation', 'fixed-term'], true));

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('contract_type') === 'indefinite') {
            $this->merge(['contract_end_date' => null]);
        }
    }
}
