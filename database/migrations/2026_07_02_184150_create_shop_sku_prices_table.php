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
        Schema::create('shop_sku_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')
                ->constrained('shops')
                ->restrictOnDelete();
            $table->foreignId('product_sku_id')
                ->constrained('product_skus')
                ->restrictOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['shop_id', 'product_sku_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_sku_prices');
    }
};
