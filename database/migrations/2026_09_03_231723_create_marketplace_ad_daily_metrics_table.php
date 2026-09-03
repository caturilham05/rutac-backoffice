<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_ad_daily_metrics', function (Blueprint $table) {
            $table->id();

            $table->foreignId('marketplace_id')
                ->constrained('marketplaces')
                ->restrictOnDelete();

            $table->date('metric_date');

            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);

            $table->decimal('ctr', 10, 6)->default(0);

            $table->unsignedBigInteger('direct_orders')->default(0);
            $table->unsignedBigInteger('broad_orders')->default(0);

            $table->decimal(
                'direct_conversion_rate',
                10,
                6
            )->default(0);

            $table->decimal(
                'broad_conversion_rate',
                10,
                6
            )->default(0);

            $table->unsignedBigInteger(
                'direct_items_sold'
            )->default(0);

            $table->unsignedBigInteger(
                'broad_items_sold'
            )->default(0);

            $table->decimal('direct_gmv', 20, 2)->default(0);
            $table->decimal('broad_gmv', 20, 2)->default(0);
            $table->decimal('expense', 20, 2)->default(0);

            $table->decimal(
                'cost_per_conversion',
                20,
                4
            )->default(0);

            $table->decimal('direct_roas', 12, 4)->default(0);
            $table->decimal('broad_roas', 12, 4)->default(0);

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['marketplace_id', 'metric_date'],
                'marketplace_ads_marketplace_date_unique'
            );

            $table->index(
                ['metric_date', 'marketplace_id'],
                'marketplace_ads_date_marketplace_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_ad_daily_metrics');
    }
};
