<?php

namespace App\Http\Controllers;

use App\Models\ProductDiscount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductDiscountController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'discount_name' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'sort' => ['nullable', Rule::in(['discount_name', 'status', 'start_date'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);

        $sort = $filters['sort'] ?? 'discount_name';
        $direction = $filters['direction'] ?? 'desc';

        $discounts = ProductDiscount::query()
            ->when($filters['discount_name'] ?? null, fn ($query, $name) => $query->where('discount_name', 'like', "%{$name}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('start_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('end_date', '<=', $date))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Backoffice/Products/ProductDiscount', [
            'discounts' => $discounts,
            'filters' => collect($filters)->only(['discount_name', 'status', 'start_date', 'end_date']),
            'sortColumn' => $sort,
            'sortDirection' => $direction,
        ]);
    }
}
