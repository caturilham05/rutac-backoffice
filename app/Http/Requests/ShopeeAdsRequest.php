<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Validator;

class ShopeeAdsRequest extends ShopeeAdsIndexRequest
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
            ...parent::rules(),
            'return_to_detail' => ['sometimes', 'boolean'],
            'campaign_id' => ['required', 'integer'],
            'edit_action' => ['required', 'in:pause,resume'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $marketplace = $this->route('marketplace');
            if (! $marketplace->access_token || ! $marketplace->shop_id) {
                $validator->errors()->add('marketplace', 'Toko Shopee belum terhubung dengan benar.');
            }
        }];
    }

    public function listQuery(): array
    {
        return $this->safe()->only(array_keys(parent::rules()));
    }

    public function message(): array
    {
        return [
            '*.required' => ':attribute tidak boleh kosong',
            '*.integer' => ':attribute harus angka',
        ];
    }

    public function attributes(): array
    {
        return [
            'campaign_id' => 'Campaign Id',
            'edit_action' => 'Action',
        ];
    }
}
