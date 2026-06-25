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
        Schema::table('purchase_products', function (Blueprint $table) {
            $table->bigInteger('purchase_id')->unsigned();
            $table->index('purchase_id');
            $table->foreign('purchase_id')->references('id')->on('purchases')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_products', function (Blueprint $table) {
            //
        });
    }
};
