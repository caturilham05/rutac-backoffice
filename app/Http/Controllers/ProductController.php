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
    public function index(): Response
    {
        return Inertia::render('Backoffice/Products/Products', [
            'products' => Product::orderBy('id', 'desc')->get()
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
        } catch (\Throwable $th) {
            return redirect()->route('products')->with('error', sprintf('error: %s. code: %s', $th->getMessage(), $th->getCode()));
        }
        return redirect()->route('products')->with('success', sprintf('product [%s] berhasil ditambahkan', $data['name'] ?? ''));
    }

    public function edit(int $id): Response
    {
        $category = Product_category::get();
        return Inertia::render('Backoffice/Products/ProductsAction', [
            'products'   => Product::findOrFail($id),
            'categories' => $category
        ]);
    }

    public function put(int $id, ProductRequest $request): RedirectResponse
    {
        $data             = $request->validated();
        $category         = Product_category::findOrFail($data['cat_id']);
        $data['cat_name'] = $category->name ?? '';
        $data['id']       = $id;
        Product::productUpsert($data);
        return redirect()->route('products')->with('success', sprintf('product [%s] berhasil diupdate', $data['name'] ?? ''));
    }

    public function delete(Product $product): RedirectResponse
    {
        $product->delete();
        return redirect()->route('products')->with('success', sprintf('product [%s] berhasil dihapus', $product->name ?? ''));
    }
}
