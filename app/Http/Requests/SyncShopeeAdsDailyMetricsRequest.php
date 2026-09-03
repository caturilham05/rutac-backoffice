<?php

namespace App\Http\Requests;

use App\Models\Marketplace;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncShopeeAdsDailyMetricsRequest extends FormRequest
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
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $marketplace = $this->route('marketplace');

            if (! $marketplace instanceof Marketplace
                || $marketplace->marketplace !== 'Shopee'
                || empty($marketplace->shop_id)
                || empty($marketplace->access_token)) {
                $validator->errors()->add('marketplace', 'Toko Shopee belum terhubung dengan benar.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            '*.required' => ':attribute wajib diisi.',
            '*.date_format' => ':attribute harus menggunakan format YYYY-MM-DD.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
        ];
    }
}
