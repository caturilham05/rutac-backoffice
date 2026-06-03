<?php

namespace App\Http\Controllers;

use App\Http\Requests\MarketplaceRequest;
use App\Models\Marketplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class MarketplaceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Backoffice/Marketplace/Marketplace', [
            'marketplaces' => Marketplace::OrderBy('id', 'desc')->get()
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
