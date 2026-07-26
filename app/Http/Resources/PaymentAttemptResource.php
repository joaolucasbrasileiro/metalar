<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'provider' => $this->provider,
            'method' => $this->method?->value ?? $this->method,
            'provider_reference' => $this->provider_reference,
            'status' => $this->status?->value ?? $this->status,
            'amount' => $this->amount,
            'checkout_url' => $this->checkout_url,
            'pix_qr_code' => $this->pix_qr_code,
            'pix_copy_paste' => $this->pix_copy_paste,
            'expires_at' => $this->expires_at?->toISOString(),
            'started_at' => $this->started_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'failed_at' => $this->failed_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
