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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                ->constrained('carts')
                ->cascadeOnDelete();
            $table->foreignId('product_sku_id')
                ->constrained('product_skus')
                ->restrictOnDelete();
            $table->foreignId('shop_id')
                ->constrained('shops')
                ->restrictOnDelete();
            $table->foreignId('shop_sku_promotion_id')
                ->nullable()
                ->constrained('shop_sku_promotions')
                ->restrictOnDelete();
            $table->decimal('quantity', 14, 3);
            $table->timestamps();

            $table->index(['cart_id', 'product_sku_id']);
            $table->index(['shop_id', 'product_sku_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
