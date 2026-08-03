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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice')->comment('Nomor invoice pesanan');
            $table->index('invoice');
            $table->string('waybill')->nullable()->comment('Nomor Resi');
            $table->index('waybill');
            $table->bigInteger('marketplace_id')->unsigned();
            $table->index('marketplace_id');
            $table->string('buyer_user_id')->comment('buyer user id shopee');
            $table->string('buyer_username')->comment('Nama pelanggan');
            $table->index('buyer_username');
            $table->string('buyer_phone')->comment('Nomor telepon pelanggan');
            $table->string('buyer_address')->comment('Alamat pelanggan');
            $table->string('courier')->comment('Nama kurir pengiriman');
            $table->integer('qty')->default(0)->comment('Jumlah produk dalam pesanan');
            $table->integer('discount')->default(0)->comment('Total diskon untuk pesanan');
            $table->integer('total_price')->default(0)->comment('Total harga pesanan setelah diskon dan ongkir');
            $table->string('status')->default('pending')->comment('Status pesanan (pending, processed, shipped, delivered, cancelled)');
            $table->index('status');
            $table->dateTime('order_time')->nullable()->default(NULL)->comment('Tanggal order dari shopee');
            $table->index('order_time');
            $table->string('payment_method')->nullable()->comment('metode pembayaran')->default(NULL);
            $table->index('payment_method');
            $table->text('notes')->nullable()->default(NULL);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
