<?php

namespace App\Services\Payments;

use App\Enums\PaymentAttemptStatus;
use App\Models\AbacatePayWebhookEvent;
use App\Models\PaymentAttempt;
use App\Services\PaymentAttemptService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Throwable;

class AbacatePayWebhookService
{
    public function __construct(
        private readonly PaymentAttemptService $paymentAttemptService,
    ) {}

    public function handle(array $payload): AbacatePayWebhookEvent
    {
        $providerEventId = $this->providerEventId($payload);
        $eventName = $this->eventName($payload);
        $providerReference = $this->providerReference($payload);

        $event = $this->prepareEvent(
            providerEventId: $providerEventId,
            eventName: $eventName,
            providerReference: $providerReference,
            payload: $payload,
        );

        if ($event->processed_at) {
            return $event->load('paymentAttempt.order');
        }

        try {
            return DB::transaction(function () use ($providerEventId, $providerReference, $eventName, $payload): AbacatePayWebhookEvent {
                $event = AbacatePayWebhookEvent::query()
                    ->where('provider_event_id', $providerEventId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($event->processed_at) {
                    return $event->load('paymentAttempt.order');
                }

                $paymentAttempt = $this->paymentAttempt($providerReference);
                $this->processEvent($eventName, $paymentAttempt, $payload);

                $event->update([
                    'payment_attempt_id' => $paymentAttempt->id,
                    'processed_at' => now(),
                    'failed_at' => null,
                    'error_message' => null,
                ]);

                return $event->refresh()->load('paymentAttempt.order');
            });
        } catch (Throwable $exception) {
            AbacatePayWebhookEvent::query()
                ->where('provider_event_id', $providerEventId)
                ->update([
                    'failed_at' => now(),
                    'error_message' => $exception->getMessage(),
                ]);

            throw $exception;
        }
    }

    private function prepareEvent(
        string $providerEventId,
        string $eventName,
        ?string $providerReference,
        array $payload,
    ): AbacatePayWebhookEvent {
        return DB::transaction(function () use ($providerEventId, $eventName, $providerReference, $payload): AbacatePayWebhookEvent {
            $event = AbacatePayWebhookEvent::query()
                ->where('provider_event_id', $providerEventId)
                ->lockForUpdate()
                ->first();

            if (! $event) {
                return AbacatePayWebhookEvent::query()->create([
                    'provider_event_id' => $providerEventId,
                    'event' => $eventName,
                    'provider_reference' => $providerReference,
                    'payload' => $payload,
                ]);
            }

            if ($event->processed_at) {
                return $event;
            }

            $event->update([
                'event' => $eventName,
                'provider_reference' => $providerReference,
                'payload' => $payload,
                'failed_at' => null,
                'error_message' => null,
            ]);

            return $event->refresh();
        });
    }

    private function processEvent(string $eventName, PaymentAttempt $paymentAttempt, array $payload): void
    {
        $this->appendWebhookPayload($paymentAttempt, $payload);

        match ($eventName) {
            'transparent.completed' => $this->paymentAttemptService->approve($paymentAttempt),
            'transparent.refunded' => $this->markPaymentAttempt($paymentAttempt, PaymentAttemptStatus::REFUNDED),
            'transparent.disputed' => $this->markPaymentAttempt($paymentAttempt, PaymentAttemptStatus::DISPUTED),
            'transparent.lost' => $this->markPaymentAttempt($paymentAttempt, PaymentAttemptStatus::LOST),
            default => null,
        };
    }

    private function paymentAttempt(?string $providerReference): PaymentAttempt
    {
        if ($providerReference === null || $providerReference === '') {
            throw (new ModelNotFoundException)->setModel(PaymentAttempt::class);
        }

        return PaymentAttempt::query()
            ->where('provider', 'abacatepay')
            ->where('provider_reference', $providerReference)
            ->firstOrFail();
    }

    private function appendWebhookPayload(PaymentAttempt $paymentAttempt, array $payload): void
    {
        $paymentAttempt->update([
            'raw_response' => [
                ...($paymentAttempt->raw_response ?? []),
                'last_webhook' => $payload,
            ],
        ]);
    }

    private function markPaymentAttempt(
        PaymentAttempt $paymentAttempt,
        PaymentAttemptStatus $status,
    ): void {
        $updates = [
            'status' => $status,
        ];

        if ($status === PaymentAttemptStatus::LOST) {
            $updates['failed_at'] = now();
        }

        $paymentAttempt->update($updates);
    }

    private function providerEventId(array $payload): string
    {
        return (string) (data_get($payload, 'id') ?: hash('sha256', json_encode($payload)));
    }

    private function eventName(array $payload): string
    {
        return (string) (data_get($payload, 'event') ?: data_get($payload, 'type', 'unknown'));
    }

    private function providerReference(array $payload): ?string
    {
        return data_get($payload, 'data.transparent.id')
            ?: data_get($payload, 'data.id')
            ?: data_get($payload, 'transparent.id');
    }
}
