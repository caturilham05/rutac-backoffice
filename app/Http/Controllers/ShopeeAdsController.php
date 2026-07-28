<?php

namespace App\Http\Controllers;

use App\Models\AdsShopee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShopeeAdsController extends Controller
{
    public function index(Request $request)
    {
        $ads = AdsShopee::getAdsShopeePaginated(10);

        return Inertia::render('Backoffice/Configuration/AdsShopee', [
            'ads' => $ads,
        ]);
    }
}
