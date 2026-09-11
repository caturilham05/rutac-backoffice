<?php

use App\Models\ConfigFee;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_sku;
use App\Models\Product_variant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

uses(TestCase::class);

it('redirects guests to login without database or Shopee requests', function (string $name) {
    Http::preventStrayRequests();
    DB::listen(fn () => throw new RuntimeException('Unexpected database query.'));

    $this->get(route($name))->assertRedirect(route('login'));

    Http::assertNothingSent();
})->with(['shopee.calculator.index', 'shopee.calculator.products']);

it('returns 422 for missing search inputs without database access', function () {
    $this->actingAs(User::factory()->make());
    DB::listen(fn () => throw new RuntimeException('Unexpected database query.'));

    $this->getJson(route('shopee.calculator.products'))->assertUnprocessable()
        ->assertJsonValidationErrors([
            'marketplace_id' => 'Pilih toko Shopee terlebih dahulu.',
            'q' => 'Masukkan kata pencarian.',
        ]);
});

describe('queries on an existing isolated test schema', function () {
    beforeEach(function () {
        $database = getenv('SHOPEE_CALCULATOR_TEST_DATABASE');
        if (! $database || ! is_file($database) || ! str_ends_with($database, '.testing.sqlite') || realpath($database) === realpath(config('database.connections.sqlite.database'))) {
            $this->markTestSkipped('Requires a separate existing SQLite test schema via SHOPEE_CALCULATOR_TEST_DATABASE; no migrations are run.');
        }
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $database]);
        DB::purge('sqlite');
        DB::beginTransaction();
        $this->beforeApplicationDestroyed(fn () => DB::rollBack());
        $this->actingAs(User::factory()->make());
        Http::preventStrayRequests();
    });

    it('renders only Shopee stores and whitelisted fee data without writes', function () {
        $store = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Calculator Test', 'access_token' => 'secret']);
        Marketplace::create(['marketplace' => 'Other', 'store' => 'Other Test']);
        $fee = ConfigFee::create(['marketplace_id' => $store->id, 'admin_fee' => 8.25]);
        DB::listen(function ($query) {
            expect(strtolower(ltrim($query->sql)))->toStartWith('select');
        });

        $this->get(route('shopee.calculator.index'))->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/Configuration/ShopeeCalculator')
            ->where('marketplaces', function ($stores) use ($store, $fee) {
                $entry = collect($stores)->firstWhere('id', $store->id);
                expect(array_keys($entry))->toBe(['id', 'store', 'config_fees']);
                expect($entry['config_fees'][0]['id'])->toBe($fee->id);
                expect(collect($stores)->pluck('store')->all())->not->toContain('Other Test');

                return true;
            }));

        Http::assertNothingSent();
    });

    it('searches the correct SKU across names and hides invalid prices and other stores', function (string $query) {
        $store = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Calculator Test']);
        $other = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Other Test']);
        $product = Product::create(['name' => 'Calculator Perfume', 'cat_id' => 0, 'cat_name' => 'Test', 'marketplace_id' => $store->id]);
        $variant = Product_variant::create(['product_id' => $product->id, 'name' => 'Vanilla']);
        $valid = Product_sku::create(['product_id' => $product->id, 'product_variant_id' => $variant->id, 'name' => 'CALC-01', 'original_price' => 45896, 'discount_price' => 80999, 'stock' => 0]);
        foreach ([null, 0, -1] as $price) {
            Product_sku::create(['product_id' => $product->id, 'name' => 'CALC-INVALID', 'original_price' => $price, 'discount_price' => 80999]);
            Product_sku::create(['product_id' => $product->id, 'name' => 'CALC-INVALID', 'original_price' => 45896, 'discount_price' => $price]);
        }
        $foreign = Product::create(['name' => 'Calculator Perfume', 'cat_id' => 0, 'cat_name' => 'Test', 'marketplace_id' => $other->id]);
        Product_sku::create(['product_id' => $foreign->id, 'name' => 'CALC-01', 'original_price' => 1, 'discount_price' => 2]);
        DB::listen(function ($query) {
            expect(strtolower(ltrim($query->sql)))->toStartWith('select');
        });

        $this->getJson(route('shopee.calculator.products', ['marketplace_id' => $store->id, 'q' => $query]))
            ->assertExactJson([['sku_id' => $valid->id, 'product_id' => $product->id, 'product_name' => 'Calculator Perfume', 'variant_name' => 'Vanilla', 'sku' => 'CALC-01', 'original_price' => 45896, 'discount_price' => 80999]]);

        Http::assertNothingSent();
    })->with(['Calculator', 'Vanilla', 'CALC-01']);

    it('rejects non-Shopee stores and search terms outside the length limits', function () {
        $store = Marketplace::create(['marketplace' => 'Other', 'store' => 'Other Test']);

        $this->getJson(route('shopee.calculator.products', ['marketplace_id' => $store->id, 'q' => 'ab']))
            ->assertUnprocessable()->assertJsonValidationErrors(['marketplace_id' => 'Toko Shopee yang dipilih tidak valid.']);
        $store->update(['marketplace' => 'Shopee']);
        foreach (['a', str_repeat('a', 101)] as $query) {
            $this->getJson(route('shopee.calculator.products', ['marketplace_id' => $store->id, 'q' => $query]))
                ->assertUnprocessable()->assertJsonValidationErrors('q');
        }
    });

    it('limits results to 20 in product name then SKU order, retaining distinct SKU identities', function () {
        $store = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Calculator Test']);
        $last = Product::create(['name' => 'Order Test Z', 'cat_id' => 0, 'cat_name' => 'Test', 'marketplace_id' => $store->id]);
        Product_sku::create(['product_id' => $last->id, 'name' => 'ORDER-Z', 'original_price' => 1, 'discount_price' => 2]);
        $first = Product::create(['name' => 'Order Test A', 'cat_id' => 0, 'cat_name' => 'Test', 'marketplace_id' => $store->id]);
        $ids = [];
        for ($index = 0; $index < 21; $index++) {
            $ids[] = Product_sku::create(['product_id' => $first->id, 'name' => 'ORDER-'.$index, 'original_price' => 1, 'discount_price' => 2])->id;
        }

        $response = $this->getJson(route('shopee.calculator.products', ['marketplace_id' => $store->id, 'q' => 'Order Test']))
            ->assertJsonCount(20);

        expect(array_column($response->json(), 'sku_id'))->toBe(array_slice($ids, 0, 20));
    });
});
