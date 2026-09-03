<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopeeWebhookRequest;
use App\Jobs\ProcessShopeeWebhook;
use Illuminate\Http\JsonResponse;

class ShopeeWebhookController extends Controller
{
    public function handle(ShopeeWebhookRequest $request): JsonResponse
    {
        ProcessShopeeWebhook::dispatch($request->validated())
            ->onConnection('redis')
            ->onQueue('shopee');

        return response()->json(['status' => 'success']);
    }
}
