<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ads_shopees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('campaign_id')->nullable()->unsigned();
            $table->string('type')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('bidding_method')->nullable();
            $table->string('campaign_placement')->nullable();
            $table->double('campaign_budget')->nullable()->unsigned();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->bigInteger('item_id')->nullable()->unsigned();
            $table->double('roas_target')->nullable()->unsigned();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_shopees');
    }
};
