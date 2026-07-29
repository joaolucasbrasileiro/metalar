<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_skus', function (Blueprint $table): void {
            $table->string('variant_name')->nullable()->after('barcode');
        });
    }

    public function down(): void
    {
        Schema::table('product_skus', function (Blueprint $table): void {
            $table->dropColumn('variant_name');
        });
    }
};
