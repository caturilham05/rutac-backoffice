<?php

namespace App\Models;

use App\Services\Shopee\ShopeeServices;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AdsShopee extends Model
{
    protected $table = 'ads_shopees';

    protected $fillable = ['marketplace_id', 'campaign_id', 'type', 'name', 'status', 'bidding_method', 'campaign_placement', 'campaign_budget', 'start_time', 'end_time', 'item_id', 'roas_target', 'enhanced_cpc'];

    public function marketplace()
    {
        return $this->belongsTo(Marketplace::class);
    }

    public static function getAdsShopeePaginated($perPage = 10, $campaignName = null, $status = null, $sort = null, $direction = 'asc')
    {
        $query = self::with('marketplace:id,store');

        if ($campaignName) {
            $query->where('name', 'like', "%{$campaignName}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($sort) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query->paginate($perPage);
    }

    protected function casts(): array
    {
        return ['enhanced_cpc' => 'boolean'];
    }

    public static function forCampaign(Marketplace $marketplace, int $campaignId): self
    {
        abort_unless($marketplace->marketplace === 'Shopee', 404);

        return self::where('marketplace_id', $marketplace->id)->where('campaign_id', $campaignId)->firstOrFail();
    }

    public function canReadSettings(Marketplace $marketplace): bool
    {
        return $marketplace->marketplace === 'Shopee' && filled($marketplace->access_token)
            && (int) $marketplace->shop_id > 0 && filled(config('services.shopee.host'))
            && (int) config('services.shopee.partner_id') > 0 && filled(config('services.shopee.partner_key'))
            && Schema::hasColumn($this->getTable(), 'enhanced_cpc');
    }

    /** The response supplied with the PRD places this boolean in manual_bidding_info. */
    public static function enhancedCpcFromCampaign(array $campaign): ?bool
    {
        $value = $campaign['manual_bidding_info']['enhanced_cpc'] ?? null;

        return is_bool($value) ? $value : null;
    }

    /** @return array{campaign_budget: ?float, roas_target: ?float, enhanced_cpc: ?bool, bidding_method: string, status: ?string} */
    public function readSettings(Marketplace $marketplace, ShopeeServices $shopee): array
    {
        if (! $this->canReadSettings($marketplace)) {
            throw new \RuntimeException('Koneksi Shopee atau kolom enhanced_cpc belum tersedia. Operator perlu memeriksa koneksi dan menjalankan migration yang sudah tersedia.');
        }

        $campaign = $shopee->getProductLevelCampaignSettingInfo($marketplace->access_token, (int) $marketplace->shop_id, (int) $this->campaign_id)[0] ?? [];
        $common = $campaign['common_info'] ?? [];
        if (! is_string($common['bidding_method'] ?? null) || ! is_string($common['campaign_status'] ?? null)) {
            throw new \RuntimeException('Respons pengaturan campaign tidak lengkap.');
        }
        $budget = $common['campaign_budget'] ?? null;
        $roas = $campaign['auto_bidding_info']['roas_target'] ?? null;

        return [
            'campaign_budget' => is_numeric($budget) && is_finite((float) $budget) ? (float) $budget : null,
            'roas_target' => is_numeric($roas) && is_finite((float) $roas) ? (float) $roas : null,
            'enhanced_cpc' => self::enhancedCpcFromCampaign($campaign),
            'bidding_method' => $common['bidding_method'],
            'status' => $common['campaign_status'],
        ];
    }

    /** @return array{settings: ?array, error: ?string} */
    public function settingsForDisplay(Marketplace $marketplace): array
    {
        if (! $this->canReadSettings($marketplace)) {
            return ['settings' => null, 'error' => 'Koneksi Shopee atau kolom enhanced_cpc belum tersedia. Operator perlu memeriksa koneksi dan menjalankan migration yang sudah tersedia.'];
        }

        return ['settings' => [
            'campaign_budget' => $this->campaign_budget === null ? null : (float) $this->campaign_budget,
            'roas_target' => $this->roas_target === null ? null : (float) $this->roas_target,
            'enhanced_cpc' => $this->enhanced_cpc,
            'bidding_method' => $this->bidding_method,
            'status' => $this->status,
        ], 'error' => null];
    }

    /** @return array{field: string, value: bool|float, message: string, error: ?string} */
    public function changeSetting(Marketplace $marketplace, ShopeeServices $shopee, array $data): array
    {
        try {
            $settings = $this->readSettings($marketplace, $shopee);
        } catch (Throwable) {
            throw ValidationException::withMessages(['settings' => 'Pengaturan terbaru belum dapat dibaca. Muat ulang sebelum mencoba lagi.']);
        }
        if ($data['edit_action'] === 'change_roas_target' && $settings['bidding_method'] !== 'auto') {
            throw ValidationException::withMessages(['roas_target' => 'ROAS Target hanya dapat diubah untuk auto bidding.']);
        }
        $field = match ($data['edit_action']) {
            'change_budget' => 'budget',
            'change_roas_target' => 'roas_target',
            'change_enhanced_cpc' => 'enhanced_cpc',
        };
        $value = $field === 'enhanced_cpc' ? $data[$field] : (float) $data[$field];
        try {
            $shopee->editManualProductAds($marketplace->access_token, (int) $marketplace->shop_id, [
                'campaign_id' => (int) $this->campaign_id,
                'reference_id' => Str::uuid()->toString(),
                'edit_action' => $data['edit_action'],
                $field => $value,
            ]);
        } catch (\DomainException $exception) {
            throw ValidationException::withMessages(['settings' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                $exception->getCode() === 408 ? 'uncertain' : 'settings' => $exception->getCode() === 408
                    ? 'Hasil perubahan belum dapat dipastikan. Muat ulang pengaturan sebelum mencoba lagi.'
                    : $exception->getMessage(),
                // : 'Shopee menolak perubahan atau respons tidak valid. Nilai lokal tidak diubah. Muat ulang pengaturan sebelum mencoba lagi.',
            ]);
        }
        $column = $field === 'budget' ? 'campaign_budget' : $field;
        $error = null;
        try {
            $this->update([$column => $value]);
        } catch (Throwable) {
            $error = 'Perubahan Shopee berhasil, tetapi penyimpanan lokal gagal. Sinkronisasi lokal perlu diulang.';
        }

        return ['field' => $column, 'value' => $value, 'message' => 'Perubahan diterima Shopee.', 'error' => $error];
    }

    public function saveStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    public static function syncCampaigns(Marketplace $marketplace, array $campaigns): bool
    {
        $existing = self::whereIn('campaign_id', array_column($campaigns, 'campaign_id'))->get()->keyBy('campaign_id');
        $rows = [];
        foreach ($campaigns as $campaign) {
            $common = $campaign['common_info'];
            $row = [
                'marketplace_id' => $marketplace->id,
                'campaign_id' => $campaign['campaign_id'],
                'type' => $common['ad_type'], 'name' => $common['ad_name'],
                'status' => $common['campaign_status'], 'bidding_method' => $common['bidding_method'],
                'campaign_placement' => $common['campaign_placement'], 'campaign_budget' => $common['campaign_budget'],
                'start_time' => empty($common['campaign_duration']['start_time']) ? null : date('Y-m-d H:i:s', $common['campaign_duration']['start_time']),
                'end_time' => empty($common['campaign_duration']['end_time']) ? null : date('Y-m-d H:i:s', $common['campaign_duration']['end_time']),
                'item_id' => $common['item_id_list'][0] ?? null,
                'roas_target' => $campaign['auto_bidding_info']['roas_target'] ?? null,
            ];
            $enhancedCpc = self::enhancedCpcFromCampaign($campaign);
            if ($enhancedCpc !== null) {
                $row['enhanced_cpc'] = $enhancedCpc;
            }
            $ad = $existing->get($campaign['campaign_id']);
            if ($ad && $ad->marketplace_id !== null && (int) $ad->marketplace_id !== (int) $marketplace->id) {
                throw new \RuntimeException('Campaign tidak sesuai dengan toko.');
            }
            if (! $ad || $ad->fill($row)->isDirty()) {
                $rows[] = $row;
            }
        }
        self::adsUpsert($rows);

        return $rows !== [];
    }

    public static function adsUpsert(array $data = []): void
    {
        $groups = [];
        foreach ($data as $row) {
            ksort($row);
            $groups[implode(',', array_keys($row))][] = $row;
        }
        foreach ($groups as $rows) {
            $columns = array_intersect(array_keys($rows[0]), [...(new self)->getFillable(), 'updated_at']);
            self::upsert($rows, ['campaign_id'], array_values($columns));
        }
    }
}
