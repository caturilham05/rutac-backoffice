<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('product discounts can be filtered and sorted descending', function () {
    $marketplaceId = DB::table('marketplaces')->insertGetId([
        'marketplace' => 'Shopee',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach ([
        ['discount_id' => 1, 'discount_name' => 'Promo A', 'status' => 'ongoing', 'start_date' => '2026-09-01', 'end_date' => '2026-09-30'],
        ['discount_id' => 2, 'discount_name' => 'Promo B', 'status' => 'ongoing', 'start_date' => '2026-09-02', 'end_date' => '2026-09-30'],
        ['discount_id' => 3, 'discount_name' => 'Other', 'status' => 'expired', 'start_date' => '2026-08-01', 'end_date' => '2026-08-31'],
    ] as $discount) {
        DB::table('product_discounts')->insert($discount + [
            'marketplace_id' => $marketplaceId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->actingAs(User::factory()->create())
        ->get(route('product_discounts', [
            'discount_name' => 'Promo',
            'status' => 'ongoing',
            'start_date' => '2026-09-01',
            'sort' => 'start_date',
            'direction' => 'desc',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/Products/ProductDiscount')
            ->has('discounts.data', 2)
            ->where('discounts.data.0.discount_name', 'Promo B')
            ->where('sortColumn', 'start_date')
            ->where('sortDirection', 'desc'));
});
