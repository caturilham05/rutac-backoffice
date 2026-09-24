<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShopeeWebhookRequest extends FormRequest
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
            'code' => ['required', 'integer', Rule::in([3, 4])],
            'shop_id' => ['required', 'integer'],
            'timestamp' => ['required', 'integer'],
            'data' => ['required', 'array'],
            'data.items' => ['sometimes', 'array'],
            'data.ordersn' => ['required', 'string'],
            'data.status' => ['required_if:code,3', 'string'],
            'data.tracking_no' => ['required_if:code,4', 'string', 'max:255'],
            'data.package_number' => ['sometimes', 'string'],
            'data.completed_scenario' => ['nullable', 'string'],
            'data.update_time' => ['required_if:code,3', 'integer'],
        ];
    }
}
