<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ShopeeFeeRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            // persen
            'admin_fee'      => ['required', 'numeric', 'min:0', 'max:100'],
            'free_shipping'  => ['required', 'numeric', 'min:0', 'max:100'],
            'extra_promo'    => ['required', 'numeric', 'min:0', 'max:100'],
            'affiliate'      => ['required', 'numeric', 'min:0', 'max:100'],
            'live'           => ['required', 'numeric', 'min:0', 'max:100'],
            'premi_fee'      => ['required', 'numeric', 'min:0', 'max:100'],
            'operational'    => ['required', 'numeric', 'min:0', 'max:100'],

            // rupiah
            'processing_fee' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':attribute wajib diisi',
            '*.numeric'  => ':attribute harus berupa angka',
            '*.max'      => ':attribute maksimal 100%',
            '*.min'      => ':attribute tidak boleh kurang dari 0',
        ];
    }

    public function attributes(): array
    {
        return [
            'admin_fee'      => 'Admin Fee',
            'free_shipping'  => 'Free Shipping',
            'extra_promo'    => 'Extra Promo',
            'processing_fee' => 'Processing Fee',
            'affiliate'      => 'Affiliate Fee',
            'live'           => 'Live Fee',
            'premi_fee'      => 'Premi Fee',
            'operational'    => 'Operational Fee',
        ];
    }
}
