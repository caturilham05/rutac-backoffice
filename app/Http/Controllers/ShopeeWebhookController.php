<?php

namespace App\Http\Controllers;

use App\Models\Marketplace;
use App\Models\Orders;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ShopeeWebhookController extends Controller
{
    private function resetShopeeLogIfNeeded()
    {
        $path = storage_path('logs/shopee.log');

        if (!file_exists($path)) {
            return;
        }

        $maxSize  = 10 * 1024 * 1024;  // 5 MB dalam bytes
        $fileSize = filesize($path);

        if ($fileSize >= $maxSize) {
            file_put_contents($path, '');  // kosongkan file
        }
    }

    public function handle(Request $request)
    {
        $data = $request->all();
        $code = $data['code'] ?? 0;
        if ($code == 0) {
            Log::channel('shopee')->info('Received Shopee Webhook', $data);
            return response()->json(['status' => 'no code found', 'data' => $data]);
        }

        // Reset jika sudah lebih dari 5MB
        $this->resetShopeeLogIfNeeded();

          // Tulis log ke file khusus
        Log::channel('shopee')->info('Received Shopee Webhook', $data);

          // Lakukan proses sesuai kebutuhan, misalnya memperbarui status pesanan, inventaris, dll.

        return response()->json(['status' => 'success']);
    }
}
