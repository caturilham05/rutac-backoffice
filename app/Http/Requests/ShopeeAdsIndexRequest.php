<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopeeAdsIndexRequest extends FormRequest
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
            'campaign_name' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['ongoing', 'scheduled', 'ended', 'paused', 'deleted', 'closed'])],
            'sort' => ['nullable', Rule::in(['campaign_id', 'campaign_budget', 'roas_target'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'marketplace_id' => ['nullable', 'integer', Rule::exists('marketplaces', 'id')->where('marketplace', 'Shopee')],
            'start_date' => ['nullable', 'required_with:end_date', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'required_with:start_date', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ];
    }

    public function messages(): array
    {
        return [
            '*.date_format' => ':attribute harus menggunakan format YYYY-MM-DD.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
            'marketplace_id.exists' => 'Toko Shopee yang dipilih tidak valid.',
        ];
    }
}
