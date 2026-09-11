<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeCalculatorProductsRequest;
use App\Models\Marketplace;
use App\Models\Product_sku;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class ShopeeCalculatorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Backoffice/Configuration/ShopeeCalculator', [
            'marketplaces' => Marketplace::shopeeCalculatorOptions(),
        ]);
    }

    public function products(ShopeeCalculatorProductsRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json(Product_sku::searchShopeeCalculator((int) $data['marketplace_id'], $data['q']));
    }
}
