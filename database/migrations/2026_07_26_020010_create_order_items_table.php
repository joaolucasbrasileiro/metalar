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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();
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
            $table->decimal('regular_unit_price', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('product_name');
            $table->string('product_sku');
            $table->string('shop_name');
            $table->timestamps();

            $table->index(['order_id', 'product_sku_id']);
            $table->index(['shop_id', 'product_sku_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
