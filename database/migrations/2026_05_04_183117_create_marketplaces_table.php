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
        Schema::create('marketplaces', function (Blueprint $table) {
            $table->id();
            $table->string('marketplace')->nullable();
            $table->string('store')->nullable();
            $table->bigInteger('marketplace_id')->nullable()->unsigned();
            $table->bigInteger('shop_id')->nullable()->unsigned();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('chiper')->nullable();
            $table->dateTime('token_expires_at')->nullable();
            $table->dateTime('refresh_token_expires_at')->nullable();
            $table->text('app_key')->nullable();
            $table->text('app_secret')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplaces');
    }
};
