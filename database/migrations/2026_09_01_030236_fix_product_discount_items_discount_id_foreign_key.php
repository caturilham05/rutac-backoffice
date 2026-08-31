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
        Schema::table('product_discount_items', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->foreign('discount_id')
                ->references('discount_id')
                ->on('product_discounts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_discount_items', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->foreign('discount_id')
                ->references('id')
                ->on('product_discounts')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};
