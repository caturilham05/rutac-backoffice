<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopeeCalculatorProductsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'marketplace_id' => ['bail', 'required', 'integer', Rule::exists('marketplaces', 'id')->where('marketplace', 'Shopee')],
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'marketplace_id.required' => 'Pilih toko Shopee terlebih dahulu.',
            'marketplace_id.integer' => 'Toko Shopee yang dipilih tidak valid.',
            'marketplace_id.exists' => 'Toko Shopee yang dipilih tidak valid.',
            'q.required' => 'Masukkan kata pencarian.',
            'q.string' => 'Kata pencarian harus berupa teks.',
            'q.min' => 'Masukkan minimal 2 karakter.',
            'q.max' => 'Kata pencarian maksimal 100 karakter.',
        ];
    }
}
