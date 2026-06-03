<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
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
            'name' => [
                'required',
                Rule::unique('product_categories')->where('name', $this->name)->ignore($this->id)
            ]
        ];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':attribute wajib diisi',
            '*.unique'   => ':attribute sudah terdaftar di sistem'
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Kategori',
        ];
    }
}
