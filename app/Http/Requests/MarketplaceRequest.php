<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MarketplaceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marketplace' => ['required'],
            'store' => [
                'required',
                Rule::unique('marketplaces')->where(function ($query) {
                    return $query->where('marketplace', $this->marketplace);
                })->ignore($this->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':attribute wajib diisi',
            '*.unique' => ':attribute sudah terdaftar di sistem',
        ];
    }

    public function attributes(): array
    {
        return [
            'marketplace' => 'Marketplace',
            'store' => 'Store',
        ];
    }
}
