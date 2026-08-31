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
        Schema::create('product_discount_items', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('discount_id')->unsigned();
            $table->foreign('discount_id')->references('id')->on('product_discounts')->onUpdate('cascade')->onDelete('cascade');
            $table->index('discount_id');

            $table->bigInteger('product_origin_id')->unsigned();
            $table->foreign('product_origin_id')->references('product_origin_id')->on('products')->onUpdate('cascade')->onDelete('cascade');
            $table->index('product_origin_id');

            $table->string('item_name')->nullable();

            $table->bigInteger('product_model_id')->unsigned();
            $table->foreign('product_model_id')->references('product_model_id')->on('product_skus')->onUpdate('cascade')->onDelete('cascade');
            $table->index('product_model_id');

            $table->string('model_name')->nullable();

            $table->double('model_original_price')->nullable()->unsigned();
            $table->double('model_promotion_price')->nullable()->unsigned();
            $table->double('model_normal_stock')->nullable()->unsigned();
            $table->double('model_promotion_stock')->nullable()->unsigned();

            $table->double('purchase_limit')->nullable()->unsigned();
            $table->double('item_original_price')->nullable()->unsigned();
            $table->double('item_promotion_price')->nullable()->unsigned();
            $table->double('normal_stock')->nullable()->unsigned();
            $table->double('item_promotion_stock')->nullable()->unsigned();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discount_items');
    }
};
