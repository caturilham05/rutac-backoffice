<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\Orders;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $sort = $request->input('sort', 'order_time');
        $direction = $request->input('direction', 'desc');

        $orders = Orders::filter($request->only(['invoice', 'buyer_username', 'courier', 'status', 'start_date', 'end_date']))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Backoffice/Orders/Order', [
            'orders'        => $orders,
            'filters'       => $request->only(['invoice', 'buyer_username', 'courier', 'status', 'start_date', 'end_date']),
            'sortColumn'    => $sort,
            'sortDirection' => $direction,
        ]);
    }

    public function orderSync(Request $request): Response
    {
        return Inertia::render('Backoffice/Orders/OrderSync', []);
    }
}
