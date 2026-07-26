<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'status' => $this->status?->value ?? $this->status,
            'subtotal' => $this->subtotal,
            'discount_total' => $this->discount_total,
            'total' => $this->total,
            'expires_at' => $this->expires_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'cancelled_at' => $this->cancelled_at?->toISOString(),
            'expired_at' => $this->expired_at?->toISOString(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payment_attempts' => PaymentAttemptResource::collection(
                $this->whenLoaded('paymentAttempts')
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
