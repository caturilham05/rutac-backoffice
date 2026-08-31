<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductDiscountRequest;
use App\Models\ProductDiscount;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductDiscountController extends Controller
{
    public function index(ProductDiscountRequest $request): Response
    {
        $filters = $request->validated();
        $sort = $filters['sort'] ?? 'discount_name';
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
}
