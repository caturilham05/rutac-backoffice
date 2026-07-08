<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Marketplace;
use App\Models\Product;
use App\Models\Product_category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $perPage    = $request->integer('per_page', 25);
        $filterData = $request->only(['name', 'category']);
        $sort       = $request->input('sort');
        $direction  = $request->input('direction', 'asc');

        $products   = Product::productGet(0, $perPage, $filterData, $sort, $direction);
        $categories = Product_category::select('id', 'name')->get();

        return Inertia::render('Backoffice/Products/Products', [
            'products'   => $products,
            'categories' => $categories,
            'filters'    => $filterData,
            'sort'       => $sort,
            'direction'  => $direction,
        ]);
    }

    public function create(): Response
    {
        $category = Product_category::get();
        return Inertia::render('Backoffice/Products/ProductsAction', [
            'products'   => (object)[],
            'categories' => $category,
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data             = $request->validated();
        $category         = Product_category::findOrFail($data['cat_id']);
        $data['cat_name'] = $category->name ?? '';

        try {
            Product::productUpsert($data);
            return redirect()->route('products')->with('success', sprintf('product [%s] berhasil ditambahkan', $data['name'] ?? ''));
        } catch (\Throwable $th) {
            dd($th);
            return redirect()->route('products')->with('error', sprintf('error: %s. code: %s', $th->getMessage(), $th->getCode()));
        }
    }

    public function edit(int $id): Response
    {
        $category        = Product_category::get();
        $products        = Product::with(['category', 'variants.skus', 'skus'])->findOrFail($id);
        $products->items = collect();

        if ($products->has_variant)
        {
            $products->items = $products->variants->map(function($variant) {
                return [
                    'id'         => $variant->id,
                    'product_id' => $variant->product_id,
                    'name'       => $variant->name,
                    'sku_id'     => $variant->skus?->id,
                    'sku'        => $variant->skus?->name,
                    'stock'      => $variant->skus?->stock,
                    'price'      => $variant->skus?->original_price,
                ];
            });
            $products->unsetRelation('variants');
            $products->unsetRelation('skus');
        } else {
            $products->sku_id = $products->skus?->id;
            $products->sku    = $products->skus?->name;
            $products->price  = $products->skus?->original_price;
            $products->stock  = $products->skus?->stock;
        }

        return Inertia::render('Backoffice/Products/ProductsAction', [
            'products'   => $products,
            'categories' => $category
        ]);
    }

    public function put(int $id, ProductRequest $request): RedirectResponse
    {
        $data             = $request->validated();
        $category         = Product_category::findOrFail($data['cat_id']);
        $data['cat_name'] = $category->name ?? '';
        $data['id']       = $id;
        try {
            Product::productUpsert($data);
            return redirect()->route('products')->with('success', sprintf('product [%s] berhasil diupdate', $data['name'] ?? ''));
        } catch (\Throwable $th) {
            return redirect()->route('products')->with('error', sprintf('error: %s. code: %s', $th->getMessage(), $th->getCode()));
        }
    }

    public function delete(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products')->with('success', sprintf('product [%s] berhasil dihapus', $product->name ?? ''));
    }
}
