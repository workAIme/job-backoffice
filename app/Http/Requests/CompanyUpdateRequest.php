<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanyUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'company-owner'], true);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = $this->route('company') ?? $this->user()?->company?->id;

        return [
            'name' => [
                'bail',
                'required',
                'string',
                'max:255',
                Rule::unique('companies', 'name')->ignore($companyId),
            ],
            'address' => ['required', 'string', 'max:255'],
            'industry' => ['required', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];
    }
}
