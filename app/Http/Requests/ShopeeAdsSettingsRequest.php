<?php

namespace App\Http\Requests;

use App\Models\AdsShopee;
use App\Models\Marketplace;
use Closure;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ShopeeAdsSettingsRequest extends ShopeeAdsIndexRequest
{
    public function authorize(): bool
    {
        $marketplace = $this->route('marketplace');
        $ad = $this->route('ad');
        abort_unless($marketplace instanceof Marketplace && $ad instanceof AdsShopee
            && $marketplace->marketplace === 'Shopee'
            && (int) $ad->marketplace_id === (int) $marketplace->id, 404);

        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('GET')) {
            return parent::rules();
        }

        $validAmount = function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_numeric($value) || ! is_finite((float) $value)
                || ($attribute === 'budget' ? (float) $value < 0 : (float) $value <= 0)) {
                $fail($attribute === 'budget'
                    ? 'Modal harus berupa angka finite nol atau lebih. Nol berarti Tidak Terbatas.'
                    : 'Nilai harus berupa angka finite lebih dari nol.');
            }
        };

        return [
            ...parent::rules(),
            'edit_action' => ['required', Rule::in(['change_budget', 'change_roas_target', 'change_enhanced_cpc'])],
            'budget' => ['exclude_unless:edit_action,change_budget', 'required', $validAmount],
            'roas_target' => ['exclude_unless:edit_action,change_roas_target', 'required', $validAmount],
            'enhanced_cpc' => ['exclude_unless:edit_action,change_enhanced_cpc', 'required', function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_bool($value)) {
                    $fail('Pilih Aktif atau Nonaktif secara eksplisit.');
                }
            }],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if (! $this->isMethod('GET') && ! $this->route('ad')->canReadSettings($this->route('marketplace'))) {
                $validator->errors()->add('settings', 'Kredensial Shopee belum tersedia atau kolom enhanced_cpc belum terpasang. Hubungi operator untuk memeriksa koneksi dan menjalankan migration yang sudah tersedia.');
            }
        }];
    }

    public function listQuery(): array
    {
        return $this->safe()->only(array_keys(parent::rules()));
    }
}
