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
        Schema::create('product_discounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('marketplace_id')->unsigned();
            $table->foreign('marketplace_id')->references('id')->on('marketplaces')->onUpdate('cascade')->onDelete('cascade');
            $table->index('marketplace_id');
            $table->bigInteger('discount_id')->unsigned();
            $table->index('discount_id');
            $table->string('discount_name');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('status')->nullable();
            $table->index(['start_date','end_date'], 'start_date_end_date_group_index');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_discounts');
    }
};
