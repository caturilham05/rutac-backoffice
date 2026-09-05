<?php

use App\Models\Marketplace;
use App\Models\MarketplaceAdDailyMetric;
use App\Models\Orders;
use App\Models\Purchase;
use App\Models\Purchase_product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('dashboard charts aggregate daily amounts within the inclusive range', function () {
    $marketplace = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Main']);
    $other = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Other']);
    foreach ([['2026-09-01 00:00:00', 'completed', 100], ['2026-09-01 12:00:00', 'completed', 200], ['2026-09-03 23:59:59', 'completed', 400], ['2026-09-02 12:00:00', 'pending', 999], ['2026-08-31 23:59:59', 'completed', 999], ['2026-09-04 00:00:00', 'completed', 999]] as $index => [$date, $status, $price]) {
        Orders::create([
            'invoice' => "order-$index", 'marketplace_id' => $marketplace->id,
            'buyer_user_id' => 'buyer', 'buyer_phone' => '123', 'buyer_address' => 'Address',
            'courier' => 'Courier', 'order_time' => $date, 'status' => $status, 'total_price' => $price,
        ]);
    }
    foreach ([['2026-09-01', 100, 20, 5], ['2026-09-01', 200, 0, 10], ['2026-09-03', 50, 0, 0], ['2026-08-31', 999, 0, 0], ['2026-09-04', 999, 0, 0]] as [$date, $price, $discount, $fee]) {
        Purchase::create(['purchase_date' => $date, 'price' => $price, 'discount' => $discount, 'additional_fee' => $fee]);
    }
    foreach ([[$marketplace, '2026-09-01', 10.25], [$other, '2026-09-01', 20.50], [$marketplace, '2026-09-03', 0], [$marketplace, '2026-08-31', 999], [$marketplace, '2026-09-04', 999]] as [$store, $date, $expense]) {
        MarketplaceAdDailyMetric::create(['marketplace_id' => $store->id, 'metric_date' => $date, 'expense' => $expense]);
    }

    $this->actingAs(User::factory()->create())->get(route('dashboard', ['start_date' => '2026-09-01', 'end_date' => '2026-09-03']))
        ->assertInertia(fn (Assert $page) => $page->component('Backoffice/Dashboard')
            ->where('daily_chart', [
                ['date' => '2026-09-01', 'order_revenue' => 300, 'purchase_total' => 295, 'ad_expense' => 30.75],
                ['date' => '2026-09-02', 'order_revenue' => 0, 'purchase_total' => 0, 'ad_expense' => null],
                ['date' => '2026-09-03', 'order_revenue' => 400, 'purchase_total' => 50, 'ad_expense' => 0],
            ]));
});

test('dashboard top purchase products filters by parfum category id regardless of category name', function () {
    $purchase = Purchase::create(['purchase_date' => '2026-09-01', 'price' => 10300, 'discount' => 0, 'additional_fee' => 0]);
    foreach ([['parfum', 3], ['bottle', 100]] as $index => [$category, $qty]) {
        Purchase_product::create([
            'purchase_id' => $purchase->id,
            'product_id' => $index + 1,
            'product_name' => $category,
            'cat_id' => $index + 1,
            'cat_name' => 'parfum',
            'price' => 100,
            'qty' => $qty,
        ]);
    }

    $this->actingAs(User::factory()->create())->get(route('dashboard', ['start_date' => '2026-09-01', 'end_date' => '2026-09-01']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('top_purchase_products', 1)
            ->where('top_purchase_products.0.product_name', 'parfum')
            ->where('top_purchase_products.0.total_qty', 3)
            ->where('top_purchase_products.0.total_price', 300));
});

test('dashboard defaults to today with no transactions', function () {
    $this->travelTo('2026-09-05 12:00:00');
    $this->actingAs(User::factory()->create())->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->has('daily_chart', 1)
            ->where('daily_chart.0', ['date' => '2026-09-05', 'order_revenue' => 0, 'purchase_total' => 0, 'ad_expense' => null]));
});

test('dashboard rejects invalid chart dates', function (array $filters, string $field) {
    $this->actingAs(User::factory()->create())->get(route('dashboard', $filters))->assertSessionHasErrors($field);
})->with([
    [['start_date' => 'invalid', 'end_date' => '2026-09-03'], 'start_date'],
    [['start_date' => '2026-09-01', 'end_date' => 'invalid'], 'end_date'],
    [['start_date' => '2026-09-03', 'end_date' => '2026-09-01'], 'end_date'],
]);

test('guests cannot access dashboard charts', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
