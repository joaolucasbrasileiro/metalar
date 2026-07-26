<?php

use App\Enums\PaymentAttemptStatus;
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
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->string('provider', 80);
            $table->string('provider_reference')->nullable();
            $table->string('status', 40)->default(PaymentAttemptStatus::PENDING->value);
            $table->decimal('amount', 12, 2);
            $table->string('checkout_url')->nullable();
            $table->text('pix_qr_code')->nullable();
            $table->text('pix_copy_paste')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('idempotency_key')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_reference']);
            $table->index(['order_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
