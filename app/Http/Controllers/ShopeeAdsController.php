<?php

namespace App\Http\Controllers;

use App\Models\AdsShopee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopeeAdsController extends Controller
{
    public function index(Request $request)
    {
        $ads = AdsShopee::getAdsShopeePaginated(
            100,
            $request->query('campaign_name'),
            $request->query('status'),
            $request->query('sort'),
            $request->query('direction', 'asc')
        );

        $campaigns = AdsShopee::select('name')->distinct()->get()->map(function ($item) {
            return ['value' => $item->name, 'label' => $item->name];
        });

        return Inertia::render('Backoffice/Configuration/AdsShopee', [
            'ads' => $ads,
            'filters' => $request->only(['campaign_name', 'status']) ?: ['campaign_name' => '', 'status' => ''],
            'sort' => $request->only(['sort', 'direction']) ?: ['sort' => null, 'direction' => 'asc'],
            'campaigns' => $campaigns,
        ]);
    }
}
