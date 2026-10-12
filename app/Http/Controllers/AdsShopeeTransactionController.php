<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdsShopeeTransactionRequest;
use App\Jobs\SyncAdsShopeeTransactions;
use App\Models\AdsShopeeTransaction;
use App\Models\Marketplace;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Inertia\Inertia;
use Inertia\Response;

class AdsShopeeTransactionController extends Controller
{
    public function index(AdsShopeeTransactionRequest $request): Response
    {
        $filters = $request->validated();

        $transactions = AdsShopeeTransaction::query()
            ->select(['id', 'invoice', 'name', 'original_price', 'actual_price', 'order_time', 'payment_method', 'payment_method_channel', 'status'])
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('order_time', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('order_time', '<=', $date))
            ->orderBy($filters['sort'] ?? 'order_time', $filters['direction'] ?? 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Backoffice/AdsTransactions/Index', [
            'transactions' => $transactions,
            'filters' => [
                'start_date' => $filters['start_date'] ?? '',
                'end_date' => $filters['end_date'] ?? '',
            ],
            'sortColumn' => $filters['sort'] ?? 'order_time',
            'sortDirection' => $filters['direction'] ?? 'desc',
        ]);
    }

    public function show(AdsShopeeTransaction $adsShopeeTransaction): Response
    {
        return Inertia::render('Backoffice/AdsTransactions/Show', [
            'transaction' => $adsShopeeTransaction->only([
                'id', 'invoice', 'name', 'original_price', 'actual_price', 'order_time',
                'order_time_formatted', 'payment_method', 'payment_method_channel',
                'transaction_fee', 'tax_amount', 'status',
            ]),
            'storeName' => Marketplace::query()->where('marketplace', 'Shopee')->value('store'),
        ]);
    }

    public function sync(): Response
    {
        return Inertia::render('Backoffice/AdsTransactions/Sync');
    }

    public function storeSync(Request $request, ShopeeServices $shopeeServices): RedirectResponse
    {
        $validated = $request->validate([
            'curl' => ['required', 'string', 'max:30000'],
        ]);

        try {
            $shopeeServices->validateAdsTransactionCurl($validated['curl']);
        } catch (\InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        SyncAdsShopeeTransactions::dispatch(Crypt::encryptString($validated['curl']))
            ->onConnection('redis')
            ->onQueue('shopee');

        return redirect()->route('ads-transactions.index')->with('success', 'Sinkronisasi transaksi iklan dimasukkan ke antrean.');
    }
}
