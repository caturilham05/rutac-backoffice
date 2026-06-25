<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
        $rules = [
            'vendor'                => ['required'],
            'products'              => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required'],
            'products.*.price'      => ['required', 'integer', 'min:0'],
            'products.*.qty'        => ['required', 'integer', 'min:0'],
        ];
        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.required'                     => ':attribute wajib diisi',
            'products.*.array'               => ':attribute harus ada datanya',
            'products.*.product_id.required' => ':attribute wajib diisi',
            'products.*.price.required'      => ':attribute wajib diisi',
            'products.*.price.integer'       => ':attribute harus berupa angka',
            'products.*.price.min'           => ':attribute minimal 0',
            'products.*.qty.required'        => ':attribute wajib diisi',
            'products.*.qty.integer'         => ':attribute harus berupa angka',
            'products.*.qty.min'             => ':attribute minimal 0',
        ];
    }

    public function attributes(): array
    {
        return [
            'vendor'                => 'Vendor',
            'products.*'            => 'Products',
            'products.*.product_id' => 'Nama Produk',
            'products.*.price'      => 'Harga',
            'products.*.qty'        => 'Quantity',
        ];
    }
}
