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
        Schema::create('ads_shopee_transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice')->comment('order sn ads');
            $table->string('name');
            $table->double('original_price')->nullable()->default(0);
            $table->double('actual_price')->nullable()->default(0);
            $table->dateTime('order_time')->nullable();
            $table->string('payment_method');
            $table->string('payment_method_channel');
            $table->double('transaction_fee')->nullable()->default(0);
            $table->double('tax_amount')->nullable()->default(0);
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_shopee_transactions');
    }
};
