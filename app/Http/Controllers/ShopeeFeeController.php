<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeFeeRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Models\ConfigFee;
use App\Models\Marketplace;

class ShopeeFeeController extends Controller
{
    public function index(Request $request): Response
    {
        $configFee = ConfigFee::with(['marketplace:id,marketplace,store'])->orderBy('id', 'desc')->get();
        return Inertia::render('Backoffice/Configuration/ShopeeFee', [
            'setting'      => $configFee,
            'marketplaces' => Marketplace::select('id', 'marketplace', 'store')->orderBy('id', 'desc')->get(),
        ]);
    }

    public function store(ShopeeFeeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        ConfigFee::shopeeFeeAction($data);
        return redirect()->route('ShopeeFee')->with('success', 'Marketplace fee berhasil ditambahkan');
    }

    public function edit(int $id): Response
    {
        return Inertia::render('Backoffice/Configuration/ShopeeFeeEdit', [
            'config_fee'   => ConfigFee::with(['marketplace:id,marketplace,store'])->where('id', $id)->first(),
            'marketplaces' => Marketplace::select('id', 'marketplace', 'store')->orderBy('id', 'desc')->get()
        ]);
    }

    public function put(int $id, ShopeeFeeRequest $request): RedirectResponse
    {
        $data       = $request->validated();
        $data['id'] = $id;
        ConfigFee::shopeeFeeAction($data);
        return redirect()->route('ShopeeFee')->with('success', 'Marketplace fee berhasil diupdate');
    }

    public function delete(ConfigFee $configFee): RedirectResponse
    {
        $configFee->delete();
        return redirect()->route('ShopeeFee')->with('success', 'Marketplace fee berhasil dihapus');
    }
}
