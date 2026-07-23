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
        Schema::create('shop_sku_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_sku_price_id')
                ->constrained('shop_sku_prices')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->decimal('promotional_price', 12, 2);
            $table->decimal('quantity_limit', 14, 3);
            $table->decimal('quantity_reserved', 14, 3)->default(0);
            $table->decimal('quantity_sold', 14, 3)->default(0);
            $table->timestamp('starts_at')->useCurrent();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_sku_promotions');
    }
};
