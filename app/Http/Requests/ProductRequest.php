<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
            'cat_id'      => ['required'],
            'name'        => ['required'],
            'description' => ['nullable', 'string'],
            'has_variant' => ['nullable', 'boolean']
        ];

        if ($this->boolean('has_variant')) {
            $rules += [
                'variants'          => ['required', 'array', 'min:1'],
                'variants.*.id'     => ['nullable', 'integer'],
                'variants.*.sku_id' => ['nullable', 'integer'],
                'variants.*.name'   => ['required'],
                'variants.*.price'  => ['required', 'numeric', 'min:0'],
                'variants.*.stock'  => ['required', 'integer', 'min:0'],
                'variants.*.sku'    => ['nullable'],
            ];
        } else {
            $rules += [
                'price'  => ['required', 'numeric', 'min:0'],
                'stock'  => ['required', 'integer', 'min:0'],
                'sku'    => ['nullable'],
                'sku_id' => ['nullable']
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            '*.required'                => ':attribute wajib diisi',
            'variants.*.name.required'  => ':attribute wajib diisi',
            'variants.*.price.required' => ':attribute wajib diisi',
            'variants.*.stock.required' => ':attribute wajib diisi',
            // 'variants'                  => 'variant tidak valid'
        ];
    }

    public function attributes(): array
    {
        return [
            'cat_id'           => 'Category',
            'name'             => 'Nama Produk',
            'price'            => 'Harga',
            'stock'            => 'Stok',
            'sku'              => 'SKU',
            'variants.*.name'  => 'Nama Variasi',
            'variants.*.price' => 'Harga Variasi',
            'variants.*.stock' => 'Stok Variasi',
        ];
    }
}
