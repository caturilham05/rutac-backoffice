<?php

use App\Models\User;
use App\Services\Shopee\ShopeeServices;
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

test('product discount filters reject invalid sorting', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('product_discounts', ['sort' => 'invalid']))
        ->assertSessionHasErrors('sort');
});

test('Shopee discount endpoint fetches the configured discount', function () {
    $marketplaceId = DB::table('marketplaces')->insertGetId([
        'marketplace' => 'Shopee',
        'marketplace_id' => 123,
        'shop_id' => 456,
        'access_token' => 'access-token',
        'app_key' => 'app-key',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shopee = Mockery::mock(ShopeeServices::class);
    $shopee->shouldReceive('getDiscount')
        ->once()
        ->with('access-token', 'app-key', 123, 456, 493849005015225)
        ->andReturn(['response' => ['discount_id' => 493849005015225]]);
    $this->app->instance(ShopeeServices::class, $shopee);

    $this->actingAs(User::factory()->create())
        ->get(route('shopee.discount', $marketplaceId))
        ->assertOk()
        ->assertJsonPath('response.discount_id', 493849005015225);
});

test('product discounts can be synchronized from every Shopee marketplace', function () {
    $marketplaceId = DB::table('marketplaces')->insertGetId([
        'marketplace' => 'Shopee',
        'marketplace_id' => 123,
        'shop_id' => 456,
        'access_token' => 'access-token',
        'app_key' => 'app-key',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shopee = Mockery::mock(ShopeeServices::class);
    $shopee->shouldReceive('getDiscountList')
        ->once()
        ->with('access-token', 'app-key', 123, 456, 'all', 1)
        ->andReturn([
            'response' => [
                'discount_list' => [[
                    'discount_id' => 789,
                ]],
                'more' => true,
            ],
        ]);
    $shopee->shouldReceive('getDiscount')
        ->once()
        ->with('access-token', 'app-key', 123, 456, 789)
        ->andReturn([
            'response' => [
                'discount_id' => 789,
                'discount_name' => 'Promo Shopee',
                'start_time' => 1788220800,
                'end_time' => 1790812800,
                'status' => 'ongoing',
                'item_list' => [[
                    'item_id' => 1001,
                    'item_name' => 'Parfum A',
                    'purchase_limit' => 2,
                    'model_list' => [[
                        'model_id' => 2001,
                        'model_name' => '50 ml',
                        'model_original_price' => 150000,
                        'model_promotion_price' => 120000,
                        'model_normal_stock' => 10,
                        'model_promotion_stock' => 5,
                    ]],
                ]],
            ],
        ]);
    $shopee->shouldReceive('getDiscountList')
        ->once()
        ->with('access-token', 'app-key', 123, 456, 'all', 2)
        ->andReturn([
            'response' => [
                'discount_list' => [[
                    'discount_id' => 790,
                ]],
                'more' => false,
            ],
        ]);
    $shopee->shouldReceive('getDiscount')
        ->once()
        ->with('access-token', 'app-key', 123, 456, 790)
        ->andReturn([
            'response' => [
                'discount_id' => 790,
                'discount_name' => 'Promo Berikutnya',
                'start_time' => 1788220800,
                'end_time' => 1790812800,
                'status' => 'upcoming',
                'item_list' => [],
            ],
        ]);
    $this->app->instance(ShopeeServices::class, $shopee);

    $this->actingAs(User::factory()->create())
        ->post(route('product_discounts.sync'))
        ->assertRedirect()
        ->assertSessionHas('success', 'Berhasil menyinkronkan 2 product discount Shopee.');

    $this->assertDatabaseHas('product_discounts', [
        'marketplace_id' => $marketplaceId,
        'discount_id' => 789,
        'discount_name' => 'Promo Shopee',
        'status' => 'ongoing',
    ]);
    $this->assertDatabaseHas('product_discounts', [
        'marketplace_id' => $marketplaceId,
        'discount_id' => 790,
        'discount_name' => 'Promo Berikutnya',
        'status' => 'upcoming',
    ]);
    $this->assertDatabaseHas('product_discount_items', [
        'discount_id' => 789,
        'product_origin_id' => 1001,
        'product_model_id' => 2001,
        'model_promotion_price' => 120000,
    ]);
});

test('product discount detail is shown by discount id', function () {
    $marketplaceId = DB::table('marketplaces')->insertGetId([
        'marketplace' => 'Shopee',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('product_discounts')->insert([
        'marketplace_id' => $marketplaceId,
        'discount_id' => 789,
        'discount_name' => 'Promo Shopee',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('product_discount_items')->insert([
        'discount_id' => 789,
        'product_origin_id' => 1001,
        'product_model_id' => 2001,
        'item_name' => 'Parfum A',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('product_discounts.show', 789))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/Products/ProductDiscountDetail')
            ->where('discount.discount_id', 789)
            ->where('items.data.0.item_name', 'Parfum A')
            ->where('items.per_page', 15));
});

test('product discount items are paginated', function () {
    $marketplaceId = DB::table('marketplaces')->insertGetId([
        'marketplace' => 'Shopee',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('product_discounts')->insert([
        'marketplace_id' => $marketplaceId,
        'discount_id' => 789,
        'discount_name' => 'Promo Shopee',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    foreach (range(1, 16) as $itemId) {
        DB::table('product_discount_items')->insert([
            'discount_id' => 789,
            'product_origin_id' => $itemId,
            'product_model_id' => $itemId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $this->actingAs(User::factory()->create())
        ->get(route('product_discounts.show', ['productDiscount' => 789, 'page' => 2]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('items.current_page', 2)
            ->has('items.data', 1));
});

test('product discount synchronization reports Shopee errors without changing data', function () {
    DB::table('marketplaces')->insert([
        'marketplace' => 'Shopee',
        'marketplace_id' => 123,
        'shop_id' => 456,
        'access_token' => 'access-token',
        'app_key' => 'app-key',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $shopee = Mockery::mock(ShopeeServices::class);
    $shopee->shouldReceive('getDiscountList')->once()->andThrow(new RuntimeException('API unavailable'));
    $this->app->instance(ShopeeServices::class, $shopee);

    $this->actingAs(User::factory()->create())
        ->post(route('product_discounts.sync'))
        ->assertRedirect()
        ->assertSessionHas('error', 'Gagal menyinkronkan product discount Shopee: API unavailable');

    $this->assertDatabaseCount('product_discounts', 0);
});
