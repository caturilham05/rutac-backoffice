<?php

namespace App\Http\Requests;

use App\Models\ProductDiscountItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductDiscountItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $discountId = $this->route('productDiscount')->discount_id;

        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_discount_items', 'id')->where('discount_id', $discountId),
            ],
            'items.*.promotion_price' => ['required', 'numeric', 'gt:0'],
            'items.*.purchase_limit' => ['required', 'integer', 'min:0'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $discountItems = ProductDiscountItem::query()
                    ->whereIn('id', collect($this->input('items'))->pluck('id'))
                    ->get()
                    ->keyBy('id');

                foreach ($this->input('items') as $index => $item) {
                    $discountItem = $discountItems[$item['id']];
                    $originalPrice = $discountItem->model_original_price ?? $discountItem->item_original_price;

                    if ($originalPrice !== null && $item['promotion_price'] > $originalPrice) {
                        $validator->errors()->add("items.{$index}.promotion_price", 'Harga promo tidak boleh melebihi harga normal.');
                    }
                }
            },
        ];
    }
}
