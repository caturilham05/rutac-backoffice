<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Override;

class ShopeeAdsRequest extends FormRequest
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
            'campaign_id' => ['required', 'integer'],
            'action'      => ['required']
        ];
    }

    public function message(): array
    {
        return [
            '*.required' => ':attribute tidak boleh kosong',
            '*.integer'  => ':attribute harus angka'
        ];
    }

    public function attributes(): array
    {
        return [
            'campaign_id' => 'Campaign Id',
            'action'      => 'Action'
        ];
    }
}
