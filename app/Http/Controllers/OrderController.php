<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Services\Shopee\ShopeeServices;
use App\Services\Shopee\ShopeeSignature;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Backoffice/Orders/Order', []);
    }

    public function orderSync(Request $request): Response
    {
        return Inertia::render('Backoffice/Orders/OrderSync', []);
    }
}
