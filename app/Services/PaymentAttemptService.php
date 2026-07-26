<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentGatewayInput;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentAttemptService
{
    public function __construct(
        private readonly PaymentGateway $paymentGateway,
        private readonly OrderService $orderService,
    ) {}

    public function createOrReuse(Order $order, PaymentGatewayInput $input): PaymentAttempt
    {
        $order->refresh();

        if ($order->status !== OrderStatus::PENDING_PAYMENT) {
            throw ValidationException::withMessages([
                'order' => 'Este pedido nao esta aguardando pagamento.',
            ]);
        }

        if ($order->expires_at && $order->expires_at->isPast()) {
            $this->orderService->expire($order);

            throw ValidationException::withMessages([
                'order' => 'Este pedido expirou e nao pode mais ser pago.',
            ]);
        }

        $reusableAttempt = $order->paymentAttempts()
            ->where('method', $input->method->value)
            ->latest()
            ->get()
            ->first(fn (PaymentAttempt $attempt) => $attempt->isReusable());

        if ($reusableAttempt) {
            return $reusableAttempt;
        }

        $idempotencyKey = (string) Str::uuid();
        $result = $this->paymentGateway->createPayment(
            $order->loadMissing(['items']),
            $idempotencyKey,
            $input,
        );

        $attempt = DB::transaction(function () use ($order, $result, $idempotencyKey, $input): PaymentAttempt {
            return $order->paymentAttempts()->create([
                'provider' => $this->paymentGateway->provider(),
                'method' => $input->method,
                'provider_reference' => $result->providerReference,
                'status' => PaymentAttemptStatus::PENDING,
                'amount' => $order->total,
                'checkout_url' => $result->checkoutUrl,
                'pix_qr_code' => $result->pixQrCode,
                'pix_copy_paste' => $result->pixCopyPaste,
                'expires_at' => $result->expiresAt,
                'started_at' => now(),
                'idempotency_key' => $idempotencyKey,
                'raw_response' => $result->rawResponse,
            ]);
        });

        if ($this->isApprovedGatewayStatus($result->status)) {
            return $this->approve($attempt);
        }

        return $attempt;
    }

    public function approve(PaymentAttempt $paymentAttempt): PaymentAttempt
    {
        return DB::transaction(function () use ($paymentAttempt): PaymentAttempt {
            $lockedAttempt = PaymentAttempt::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($paymentAttempt->id);

            if ($lockedAttempt->status === PaymentAttemptStatus::APPROVED) {
                return $lockedAttempt->load('order');
            }

            if ($lockedAttempt->status !== PaymentAttemptStatus::PENDING) {
                throw ValidationException::withMessages([
                    'payment' => 'Somente pagamentos pendentes podem ser aprovados.',
                ]);
            }

            $this->orderService->markPaid($lockedAttempt->order);

            $lockedAttempt->update([
                'status' => PaymentAttemptStatus::APPROVED,
                'approved_at' => now(),
            ]);

            return $lockedAttempt->load('order');
        });
    }

    public function reject(PaymentAttempt $paymentAttempt, string $reason = 'Pagamento recusado.'): PaymentAttempt
    {
        return DB::transaction(function () use ($paymentAttempt, $reason): PaymentAttempt {
            $lockedAttempt = PaymentAttempt::query()
                ->with('order')
                ->lockForUpdate()
                ->findOrFail($paymentAttempt->id);

            if ($lockedAttempt->status !== PaymentAttemptStatus::PENDING) {
                return $lockedAttempt->load('order');
            }

            $lockedAttempt->update([
                'status' => PaymentAttemptStatus::REJECTED,
                'failed_at' => now(),
                'raw_response' => [
                    ...($lockedAttempt->raw_response ?? []),
                    'rejection_reason' => $reason,
                ],
            ]);

            return $lockedAttempt->load('order');
        });
    }

    public function inputFromValidatedData(array $data): PaymentGatewayInput
    {
        return new PaymentGatewayInput(
            method: PaymentMethod::from($data['method']),
            installments: $data['installments'] ?? null,
            card: $this->cardPayload($data['card'] ?? []),
            customer: $this->customerPayload($data['customer'] ?? []),
        );
    }

    private function isApprovedGatewayStatus(string $status): bool
    {
        return in_array(strtoupper($status), ['APPROVED', 'COMPLETE', 'COMPLETED', 'PAID'], true);
    }

    private function cardPayload(array $card): array
    {
        if ($card === []) {
            return [];
        }

        return [
            'holderName' => $card['holder_name'],
            'number' => preg_replace('/\D+/', '', $card['number']),
            'expirationMonth' => (int) $card['expiration_month'],
            'expirationYear' => (int) $card['expiration_year'],
            'cvv' => $card['cvv'],
        ];
    }

    private function customerPayload(array $customer): array
    {
        return array_filter([
            'name' => $customer['name'] ?? null,
            'email' => $customer['email'] ?? null,
            'taxId' => isset($customer['tax_id'])
                ? preg_replace('/\D+/', '', $customer['tax_id'])
                : null,
            'cellphone' => $customer['cellphone'] ?? null,
        ]);
    }
}
