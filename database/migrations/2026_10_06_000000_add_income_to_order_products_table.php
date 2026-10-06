<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->decimal('income', 14, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn('income');
        });
    }
};
