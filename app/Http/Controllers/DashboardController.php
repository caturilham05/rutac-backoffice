<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Purchase_product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate   = $request->input('end_date', now()->toDateString());

        $purchaseStats = Purchase::whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->select([
                DB::raw('COUNT(id) as total_invoice'),
                DB::raw('SUM(price - discount + additional_fee) as total_price'),
                DB::raw('SUM(discount) as total_discount'),
                DB::raw('SUM(additional_fee) as total_additional_fee'),
            ])
            ->first();

        $orderStats = \App\Models\Orders::whereDate('order_time', '>=', $startDate)
            ->whereDate('order_time', '<=', $endDate)
            ->where('status', 'completed')
            ->select([
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(income) as total_income'),
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('SUM(discount) as total_discount'),
            ])
            ->first();

        $totalQty = Purchase_product::whereHas('purchase', function ($query) use ($startDate, $endDate) {
            $query->whereDate('purchase_date', '>=', $startDate)
                ->whereDate('purchase_date', '<=', $endDate);
        })->sum('qty');

        $topOrderProducts = \App\Models\OrderProducts::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereDate('order_time', '>=', $startDate)
                ->whereDate('order_time', '<=', $endDate)
                ->where('status', 'completed');
        })
            ->select('product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(sale * qty) as total_price'))
            ->groupBy('product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topPurchaseProducts = Purchase_product::whereHas('purchase', function ($query) use ($startDate, $endDate) {
            $query->whereDate('purchase_date', '>=', $startDate)
                ->whereDate('purchase_date', '<=', $endDate);
        })
            ->select('product_name', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(price * qty) as total_price'))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topBuyers = \App\Models\Orders::whereDate('order_time', '>=', $startDate)
            ->whereDate('order_time', '<=', $endDate)
            ->where('status', 'completed')
            ->select('buyer_username', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_price) as total_spent'))
            ->groupBy('buyer_username')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();

        $topVendors = Purchase::whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->select('vendor', DB::raw('COUNT(id) as total_invoice'), DB::raw('SUM(price - discount + additional_fee) as total_amount'))
            ->groupBy('vendor')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        $topVendors = Purchase::whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->select('vendor', DB::raw('COUNT(id) as total_invoice'), DB::raw('SUM(price - discount + additional_fee) as total_amount'))
            ->groupBy('vendor')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();

        return Inertia::render('Backoffice/Dashboard', [
            'filters' => [
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ],
            'stats' => [
                'order' => [
                    'total_orders'   => $orderStats->total_orders ?? 0,
                    'total_income'   => $orderStats->total_income ?? 0,
                    'total_revenue'  => $orderStats->total_revenue ?? 0,
                    'total_qty'      => $orderStats->total_qty ?? 0,
                    'total_discount' => $orderStats->total_discount ?? 0,
                ],
                'purchase' => [
                    'total_invoice'        => $purchaseStats->total_invoice ?? 0,
                    'total_price'          => $purchaseStats->total_price ?? 0,
                    'total_discount'       => $purchaseStats->total_discount ?? 0,
                    'total_additional_fee' => $purchaseStats->total_additional_fee ?? 0,
                    'total_qty'            => $totalQty ?? 0,
                ],
            ],
            'top_order_products'    => $topOrderProducts,
            'top_purchase_products' => $topPurchaseProducts,
            'top_buyers'            => $topBuyers,
            'top_vendors'           => $topVendors,
        ]);
    }
}
