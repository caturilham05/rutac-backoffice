<?php

namespace App\Http\Controllers;

use App\Models\MarketplaceAdDailyMetric;
use App\Models\OrderProducts;
use App\Models\Orders;
use App\Models\Purchase;
use App\Models\Purchase_product;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $request->mergeIfMissing([
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
        ]);
        $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $startDate = $request->input('start_date', now()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $dailyOrders = Orders::whereDate('order_time', '>=', $startDate)
            ->whereDate('order_time', '<=', $endDate)
            ->where('status', 'completed')
            ->selectRaw('DATE(order_time) as date, SUM(total_price) as amount')
            ->groupByRaw('DATE(order_time)')
            ->pluck('amount', 'date');

        $dailyPurchases = Purchase::whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->selectRaw('DATE(purchase_date) as date, SUM(price - discount + additional_fee) as amount')
            ->groupByRaw('DATE(purchase_date)')
            ->pluck('amount', 'date');

        $dailyAds = MarketplaceAdDailyMetric::whereDate('metric_date', '>=', $startDate)
            ->whereDate('metric_date', '<=', $endDate)
            ->selectRaw('DATE(metric_date) as date, SUM(expense) as amount')
            ->groupByRaw('DATE(metric_date)')
            ->pluck('amount', 'date');

        $dailyChart = [];
        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            $day = $date->toDateString();
            $dailyChart[] = [
                'date' => $day,
                'order_revenue' => (float) ($dailyOrders[$day] ?? 0),
                'purchase_total' => (float) ($dailyPurchases[$day] ?? 0),
                'ad_expense' => $dailyAds->has($day) ? (float) $dailyAds[$day] : null,
            ];
        }

        $purchaseStats = Purchase::whereDate('purchase_date', '>=', $startDate)
            ->whereDate('purchase_date', '<=', $endDate)
            ->select([
                DB::raw('COUNT(id) as total_invoice'),
                DB::raw('SUM(price - discount + additional_fee) as total_price'),
                DB::raw('SUM(discount) as total_discount'),
                DB::raw('SUM(additional_fee) as total_additional_fee'),
            ])
            ->first();

        $orderStats = Orders::whereDate('order_time', '>=', $startDate)
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

        $topOrderProducts = OrderProducts::whereHas('order', function ($query) use ($startDate, $endDate) {
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

        $topBuyers = Orders::whereDate('order_time', '>=', $startDate)
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
            'daily_chart' => $dailyChart,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'stats' => [
                'order' => [
                    'total_orders' => $orderStats->total_orders ?? 0,
                    'total_income' => $orderStats->total_income ?? 0,
                    'total_revenue' => $orderStats->total_revenue ?? 0,
                    'total_qty' => $orderStats->total_qty ?? 0,
                    'total_discount' => $orderStats->total_discount ?? 0,
                ],
                'purchase' => [
                    'total_invoice' => $purchaseStats->total_invoice ?? 0,
                    'total_price' => $purchaseStats->total_price ?? 0,
                    'total_discount' => $purchaseStats->total_discount ?? 0,
                    'total_additional_fee' => $purchaseStats->total_additional_fee ?? 0,
                    'total_qty' => $totalQty ?? 0,
                ],
            ],
            'top_order_products' => $topOrderProducts,
            'top_purchase_products' => $topPurchaseProducts,
            'top_buyers' => $topBuyers,
            'top_vendors' => $topVendors,
        ]);
    }
}
