<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarketplaceRequest;
use App\Models\Marketplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    protected int $set_page = 15;

    public function index(Request $request): Response
    {
        $per_page  = $request->integer('per_page', $this->set_page);
        $filters   = $request->only(['marketplace', 'store']);
        $sort      = $request->input('sort');
        $direction = $request->input('direction', 'asc');

        return Inertia::render('Backoffice/Marketplace/Marketplace', [
            'marketplaces' => Marketplace::marketplacePagination($per_page, $filters, $sort, $direction),
            'filters'      => $filters,
            'sort'         => $sort,
            'direction'    => $direction,
            'options'      => [
                'marketplace' => Marketplace::marketplaceOptions('marketplace'),
                'store'       => Marketplace::marketplaceOptions('store'),
            ],
        ]);
    }

    public function store(MarketplaceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        Marketplace::marketplaceUpsert($data);
        return redirect()->route('marketplace')->with('success', 'Marketplace berhasil ditambah');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Backoffice/Marketplace/MarketplaceEdit', [
            'marketplace' => Marketplace::findOrFail($id)
        ]);
    }

    public function put(int $id, MarketplaceRequest $request): RedirectResponse
    {
        $data       = $request->validated();
        $data['id'] = $id;
        Marketplace::marketplaceUpsert($data);

        return redirect()->route('marketplace')->with('success', 'Marketplace berhasil diupdate');
    }

    public function delete(Marketplace $marketplace): RedirectResponse
    {
        $marketplace->delete();
        return redirect()->route('marketplace')->with('success', 'Marketplace berhasil dihapus');
    }
}
