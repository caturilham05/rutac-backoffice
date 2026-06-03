<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductCategoryRequest;
use App\Models\Product_category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Backoffice/Products/ProductCategory', [
            'catList' => Product_category::orderBy('id', 'desc')->get()
        ]);
    }

    public function store(ProductCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Product_category::productCategoryUpsert($data);
        return redirect()->route('product_category')->with('success', sprintf('product category [%s] berhasil ditambahkan', $data['name'] ?? ''));
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Backoffice/Products/ProductCategoryEdit', [
            'category' => Product_category::findOrFail($id)
        ]);
    }

    public function put(int $id, ProductCategoryRequest $request): RedirectResponse
    {
        $data       = $request->validated();
        $data['id'] = $id;
        Product_category::productCategoryUpsert($data);
        return redirect()->route('product_category')->with('success', sprintf('product category [%s] berhasil diupdate', $data['name'] ?? ''));
    }

    public function delete(Product_category $categories): RedirectResponse
    {
        $categories->delete();
        return redirect()->route('product_category')->with('success', sprintf('product category [%s] berhasil dihapus', $categories->name ?? ''));
    }
}
