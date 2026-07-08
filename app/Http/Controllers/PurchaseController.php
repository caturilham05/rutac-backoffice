<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    protected $set_page = 25;

    private function generateInvoice(string $code): string
    {
        $now = now();

        $lastPurchase = Purchase::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->latest('id')
            ->first();

        $nextIncrement = 1;

        if ($lastPurchase) {
            $parts = explode('/', $lastPurchase->invoice);
            $nextIncrement = ((int) end($parts)) + 1;
        }

        return sprintf(
            '%s/RTC/%s/%s/%04d',
            $code,
            $now->format('y'),
            $now->format('m'),
            $nextIncrement
        );
    }

    public function index(Request $request): Response
    {
        $per_page    = $request->integer('per_page', $this->set_page);
        $filter_data = $request->only(['invoice', 'vendor', 'created_at']);
        $sort        = $request->input('sort');
        $direction   = $request->input('direction', 'asc');
        $purchases = Purchase::purchasePagination($per_page, $filter_data, $sort, $direction);
        // $purchases = Purchase::with(['purchase_products'])->orderBy('id', 'desc')->get();
        $purchases->map(function($purchase){
            $purchase['items'] = $purchase->purchase_products ?? [];
            $purchase->unsetRelation('purchase_products');

            return $purchase;
        });

        return Inertia::render('Backoffice/Purchases/PurchasesList', [
            'purchases' => $purchases,
            'filters'   => $filter_data,
            'sort'      => $sort,
            'direction' => $direction
        ]);
    }

    public function create(): Response
    {
        $invoice        = $this->generateInvoice('PURC');
        $products       = Product::productGet(0, 15, [], null, 'asc', false);
        return Inertia::render('Backoffice/Purchases/PurchasesCreate', [
            'invoice'  => $invoice,
            'products' => $products ?? []
        ]);
    }

    public function store(PurchaseRequest $request): RedirectResponse
    {
        $data            = $request->validated();
        $invoice         = $this->generateInvoice('PURC');
        $data['invoice'] = $invoice;

        try {
            Purchase::purchaseInsert($data);
            return redirect()->route('purchases.list')->with('success', sprintf('Pembelian [%s] berhasil ditambahkan', $data['invoice'] ?? ''));
        } catch (\Throwable $th) {
            return redirect()->route('purchases.list')->with('error', sprintf('error: %s. code: %s', $th->getMessage(), $th->getCode()));
        }
    }
}
