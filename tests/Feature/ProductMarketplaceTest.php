<?php

use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_category;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('product create provides available marketplaces without credentials', function () {
    $marketplace = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Toko Parfum', 'access_token' => 'secret']);

    $this->actingAs(User::factory()->create())->get(route('products.create'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Backoffice/Products/ProductsAction')
            ->has('marketplaces', 1)
            ->where('marketplaces.0', ['id' => $marketplace->id, 'marketplace' => 'Shopee', 'store' => 'Toko Parfum']));
});

test('product marketplace is saved and retained when editing', function () {
    $marketplace = Marketplace::create(['marketplace' => 'Shopee', 'store' => 'Toko Parfum']);
    $category = Product_category::create(['name' => 'Parfum']);
    $data = ['cat_id' => $category->id, 'name' => 'Parfum Baru', 'price' => 100000, 'stock' => 5, 'marketplace_id' => $marketplace->id];
    $this->actingAs(User::factory()->create())->post(route('products.store'), $data)
        ->assertSessionHasNoErrors()->assertRedirect(route('products'));

    $product = Product::sole();
    $this->assertDatabaseHas('products', ['id' => $product->id, 'marketplace_id' => $marketplace->id]);
    $this->get(route('products.edit', $product->id))
        ->assertInertia(fn (Assert $page) => $page
            ->where('products.marketplace_id', $marketplace->id)
            ->has('marketplaces', 1));
    $this->put(route('products.put', $product->id), $data + ['sku_id' => $product->skus()->sole()->id])
        ->assertSessionHasNoErrors()->assertRedirect(route('products'));
    $this->assertDatabaseHas('products', ['id' => $product->id, 'marketplace_id' => $marketplace->id]);
});

test('product rejects unavailable marketplace selections', function ($selection, $message) {
    $category = Product_category::create(['name' => 'Parfum']);

    $this->actingAs(User::factory()->create())->post(route('products.store'), [
        'cat_id' => $category->id, 'name' => 'Parfum', 'price' => 100000, 'stock' => 5, 'marketplace_id' => $selection,
    ])->assertSessionHasErrors(['marketplace_id' => $message]);

    $this->assertDatabaseCount('products', 0);
})->with([
    'unknown' => [999, 'Marketplace yang dipilih tidak tersedia.'],
    'invalid' => ['invalid', 'Marketplace yang dipilih tidak valid.'],
]);

test('product can still be created without a marketplace', function () {
    $category = Product_category::create(['name' => 'Parfum']);

    $this->actingAs(User::factory()->create())->post(route('products.store'), [
        'cat_id' => $category->id, 'name' => 'Parfum Lokal', 'price' => 100000, 'stock' => 5, 'marketplace_id' => '',
    ])->assertSessionHasNoErrors()->assertRedirect(route('products'));

    $this->assertDatabaseHas('products', ['name' => 'Parfum Lokal', 'marketplace_id' => 0]);
});
