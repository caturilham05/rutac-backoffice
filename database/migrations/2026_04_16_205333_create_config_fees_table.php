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
        Schema::create('config_fees', function (Blueprint $table) {
            $table->id();
            $table->double('admin_fee', 22,2)->unsigned()->default(0);
            $table->double('free_shipping', 22,2)->unsigned()->default(0);
            $table->double('extra_promo', 22,2)->unsigned()->default(0);
            $table->double('processing_fee', 22,2)->unsigned()->default(0);
            $table->double('affiliate', 22,2)->unsigned()->default(0);
            $table->double('live', 22,2)->unsigned()->default(0);
            $table->double('premi_fee', 22,2)->unsigned()->default(0);
            $table->double('operational', 22,2)->unsigned()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_fees');
    }
};
