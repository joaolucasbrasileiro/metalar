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
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity_reserved_before', 14, 3)
                ->nullable()
                ->after('quantity_after');
            $table->decimal('quantity_reserved_after', 14, 3)
                ->nullable()
                ->after('quantity_reserved_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn([
                'quantity_reserved_before',
                'quantity_reserved_after',
            ]);
        });
    }
};
