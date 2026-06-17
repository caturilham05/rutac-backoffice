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
        Schema::table('products', function (Blueprint $table) {
            $table->bigInteger('marketplace_id')->nullable()->after('id');
            $table->bigInteger('product_origin_id')->nullable()->unsigned()->comment('product id nya marketplace')->after('id');
            $table->text('description')->nullable();
            $table->tinyInteger('has_variant')->nullable()->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
