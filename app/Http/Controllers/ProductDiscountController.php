<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDiscountRequest;
use App\Http\Requests\UpdateProductDiscountItemsRequest;
use App\Models\Marketplace;
use App\Models\ProductDiscount;
use App\Models\ProductDiscountItem;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductDiscountController extends Controller
{
    public function index(ProductDiscountRequest $request): Response
    {
        $filters = $request->validated();
        $sort = $filters['sort'] ?? 'start_date';
        $direction = $filters['direction'] ?? 'desc';

        return Inertia::render('Backoffice/Products/ProductDiscount', [
            'discounts' => ProductDiscount::pagination($filters, $sort, $direction),
            'filters' => collect($filters)->only(['discount_name', 'status', 'start_date', 'end_date']),
            'sortColumn' => $sort,
            'sortDirection' => $direction,
        ]);
    }

    public function sync(ShopeeServices $shopee): RedirectResponse
    {
        try {
            $synced = ProductDiscount::synchronize($shopee);

            return back()->with('success', "Berhasil menyinkronkan {$synced} product discount Shopee.");
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Gagal menyinkronkan product discount Shopee: '.$exception->getMessage());
        }
    }

    public function show(ProductDiscount $productDiscount): Response
    {
        return Inertia::render('Backoffice/Products/ProductDiscountDetail', [
            'discount' => $productDiscount,
            'items' => $productDiscount->items()->paginate(15),
        ]);
    }

    public function edit(ProductDiscount $productDiscount): Response
    {
        return Inertia::render('Backoffice/Products/ProductDiscountDetail', [
            'discount' => $productDiscount,
            'items' => $productDiscount->items()->paginate(15),
            'editing' => true,
        ]);
    }

    public function update(
        UpdateProductDiscountItemsRequest $request,
        ProductDiscount $productDiscount,
        ShopeeServices $shopee,
    ): RedirectResponse {
        $discountItems = ProductDiscountItem::query()
            ->whereIn('id', collect($request->validated('items'))->pluck('id'))
            ->get()
            ->keyBy('id');

        $items = collect($request->validated('items'))
            ->groupBy(fn (array $item) => $discountItems[$item['id']]->product_origin_id)
            ->map(function ($rows) use ($discountItems): array {
                $first = $rows->first();
                $firstDiscountItem = $discountItems[$first['id']];
                $item = [
                    'item_id' => $firstDiscountItem->product_origin_id,
                    'purchase_limit' => (int) ($firstDiscountItem->purchase_limit ?? 0),
                ];

                if ($firstDiscountItem->product_model_id === 0) {
                    $item['item_promotion_price'] = (float) $first['promotion_price'];
                } else {
                    $item['model_list'] = $rows->map(fn (array $row): array => [
                        'model_id' => $discountItems[$row['id']]->product_model_id,
                        'model_promotion_price' => (float) $row['promotion_price'],
                    ])->values()->all();
                }

                return $item;
            })->values()->all();

        try {
            $marketplace = Marketplace::query()->findOrFail($productDiscount->marketplace_id);
            $shopee->updateDiscountItems($marketplace->access_token, $marketplace->shop_id, $productDiscount->discount_id, $items);

            foreach ($request->validated('items') as $item) {
                $discountItem = $discountItems[$item['id']];
                $discountItem->update([
                    $discountItem->product_model_id === 0 ? 'item_promotion_price' : 'model_promotion_price' => $item['promotion_price'],
                ]);
            }

            return redirect()->route('product_discounts.show', $productDiscount->discount_id)->with('success', 'Discount item berhasil diperbarui.');
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'Gagal memperbarui discount item Shopee: '.$exception->getMessage());
        }
    }
}
