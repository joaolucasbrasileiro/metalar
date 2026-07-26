<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use App\Models\ShopSkuPromotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly StockReservationService $stockReservationService,
        private readonly CartService $cartService,
    ) {}

    public function createFromCart(User $user, Cart $cart): Order
    {
        $cart->loadMissing('items');

        if (! $cart->isActive() || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'Adicione itens ao carrinho antes de criar o pedido.',
            ]);
        }

        $items = $cart->items
            ->map(fn ($item) => [
                'product_sku_id' => $item->product_sku_id,
                'shop_id' => $item->shop_id,
                'promotion_id' => $item->shop_sku_promotion_id,
                'quantity' => $item->quantity,
            ])
            ->all();

        return DB::transaction(function () use ($user, $items, $cart): Order {
            $order = $user->orders()->create([
                'status' => OrderStatus::PENDING_PAYMENT,
                'expires_at' => now()->addMinutes((int) config('commerce.order_payment_ttl_minutes')),
            ]);

            $subtotal = 0.0;
            $discountTotal = 0.0;
            $total = 0.0;

            foreach ($items as $index => $item) {
                $quantity = round((float) $item['quantity'], 3);
                $shop = Shop::query()->findOrFail($item['shop_id']);
                $productSku = ProductSku::query()
                    ->with('product')
                    ->findOrFail($item['product_sku_id']);

                $price = $this->lockPrice($shop, $productSku, $index);
                $promotion = $this->resolvePromotion(
                    price: $price,
                    promotionId: $item['promotion_id'] ?? null,
                    quantity: $quantity,
                    index: $index,
                );

                $regularUnitPrice = (float) $price->price;
                $unitPrice = $promotion
                    ? (float) $promotion->promotional_price
                    : $regularUnitPrice;
                $lineSubtotal = round($regularUnitPrice * $quantity, 2);
                $lineTotal = round($unitPrice * $quantity, 2);
                $lineDiscount = round($lineSubtotal - $lineTotal, 2);

                $order->items()->create([
                    'product_id' => $productSku->product_id,
                    'product_sku_id' => $productSku->id,
                    'shop_id' => $shop->id,
                    'shop_sku_promotion_id' => $promotion?->id,
                    'quantity' => $this->decimal($quantity, 3),
                    'regular_unit_price' => $this->decimal($regularUnitPrice, 2),
                    'unit_price' => $this->decimal($unitPrice, 2),
                    'discount_total' => $this->decimal($lineDiscount, 2),
                    'total' => $this->decimal($lineTotal, 2),
                    'product_name' => $productSku->product->name,
                    'product_sku' => $productSku->sku,
                    'shop_name' => $shop->name,
                ]);

                $this->stockReservationService->reserve(
                    shop: $shop,
                    productSku: $productSku,
                    quantity: $quantity,
                    user: $user,
                    promotion: $promotion,
                    reason: "Reserva do pedido {$order->id}",
                    reference: $order,
                );

                $subtotal += $lineSubtotal;
                $discountTotal += $lineDiscount;
                $total += $lineTotal;
            }

            $order->update([
                'subtotal' => $this->decimal($subtotal, 2),
                'discount_total' => $this->decimal($discountTotal, 2),
                'total' => $this->decimal($total, 2),
            ]);

            $this->recordStatus($order, null, OrderStatus::PENDING_PAYMENT, 'Pedido criado aguardando pagamento.');
            $this->cartService->markConverted($cart, $order->id);

            return $order->load($this->relations());
        });
    }

    public function cancel(Order $order, ?User $user = null): Order
    {
        return DB::transaction(function () use ($order, $user): Order {
            $lockedOrder = Order::query()
                ->with(['items.productSku', 'items.shop', 'items.promotion'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status !== OrderStatus::PENDING_PAYMENT) {
                throw ValidationException::withMessages([
                    'order' => 'Somente pedidos aguardando pagamento podem ser cancelados.',
                ]);
            }

            $this->releaseReservations($lockedOrder, $user, 'Pedido cancelado');

            $this->transition(
                $lockedOrder,
                OrderStatus::CANCELLED,
                'Pedido cancelado antes do pagamento.',
                ['cancelled_by_user_id' => $user?->id],
            );

            return $lockedOrder->load($this->relations());
        });
    }

    public function expire(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->with(['items.productSku', 'items.shop', 'items.promotion', 'paymentAttempts'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status !== OrderStatus::PENDING_PAYMENT) {
                return $lockedOrder->load($this->relations());
            }

            if ($lockedOrder->expires_at && $lockedOrder->expires_at->isFuture()) {
                return $lockedOrder->load($this->relations());
            }

            $this->releaseReservations($lockedOrder, null, 'Pedido expirado');

            $lockedOrder->paymentAttempts()
                ->where('status', 'pending')
                ->update([
                    'status' => 'expired',
                    'failed_at' => now(),
                ]);

            $this->transition(
                $lockedOrder,
                OrderStatus::EXPIRED,
                'Pedido expirado sem pagamento.',
            );

            return $lockedOrder->load($this->relations());
        });
    }

    public function markPaid(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()
                ->with(['items.productSku', 'items.shop', 'items.promotion'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            if ($lockedOrder->status === OrderStatus::PAID) {
                return $lockedOrder->load($this->relations());
            }

            if ($lockedOrder->status !== OrderStatus::PENDING_PAYMENT) {
                throw ValidationException::withMessages([
                    'order' => 'Somente pedidos aguardando pagamento podem ser confirmados.',
                ]);
            }

            foreach ($lockedOrder->items as $item) {
                $this->stockReservationService->commitSale(
                    shop: $item->shop,
                    productSku: $item->productSku,
                    quantity: (float) $item->quantity,
                    promotion: $item->promotion,
                    reason: "Venda confirmada do pedido {$lockedOrder->id}",
                    reference: $lockedOrder,
                );
            }

            $this->transition(
                $lockedOrder,
                OrderStatus::PAID,
                'Pagamento aprovado.',
            );

            return $lockedOrder->load($this->relations());
        });
    }

    public function expirePendingOrders(): int
    {
        $expired = 0;

        Order::query()
            ->where('status', OrderStatus::PENDING_PAYMENT->value)
            ->where('expires_at', '<=', now())
            ->orderBy('id')
            ->each(function (Order $order) use (&$expired): void {
                $this->expire($order);
                $expired++;
            });

        return $expired;
    }

    public function relations(): array
    {
        return [
            'items.product',
            'items.productSku',
            'items.shop',
            'items.promotion',
            'paymentAttempts',
            'statusHistories',
        ];
    }

    private function lockPrice(Shop $shop, ProductSku $productSku, int $index): ShopSkuPrice
    {
        $price = $shop->skuPrices()
            ->where('product_sku_id', $productSku->id)
            ->lockForUpdate()
            ->first();

        if (! $price) {
            throw ValidationException::withMessages([
                "items.{$index}.product_sku_id" => 'Nao ha preco cadastrado para este SKU nesta loja.',
            ]);
        }

        return $price;
    }

    private function resolvePromotion(
        ShopSkuPrice $price,
        ?int $promotionId,
        float $quantity,
        int $index,
    ): ?ShopSkuPromotion {
        $promotions = $price->promotions()
            ->whereNull('cancelled_at')
            ->lockForUpdate()
            ->get();

        if ($promotionId) {
            $promotion = $promotions->firstWhere('id', $promotionId);

            if (! $promotion || ! $promotion->isActive() || $promotion->remainingQuantity() < $quantity) {
                throw ValidationException::withMessages([
                    "items.{$index}.promotion_id" => 'A promocao escolhida nao esta disponivel para a quantidade solicitada.',
                ]);
            }

            return $promotion;
        }

        return $promotions
            ->filter(fn (ShopSkuPromotion $promotion) => $promotion->isActive()
                && $promotion->remainingQuantity() >= $quantity)
            ->sortBy(fn (ShopSkuPromotion $promotion) => (float) $promotion->promotional_price)
            ->first();
    }

    private function releaseReservations(Order $order, ?User $user, string $reason): void
    {
        foreach ($order->items as $item) {
            $this->stockReservationService->release(
                shop: $item->shop,
                productSku: $item->productSku,
                quantity: (float) $item->quantity,
                user: $user,
                promotion: $item->promotion,
                reason: "{$reason} {$order->id}",
                reference: $order,
            );
        }
    }

    private function transition(
        Order $order,
        OrderStatus $toStatus,
        string $reason,
        ?array $metadata = null,
    ): void {
        $fromStatus = $order->status;
        $timestamps = match ($toStatus) {
            OrderStatus::PAID => ['paid_at' => now()],
            OrderStatus::CANCELLED => ['cancelled_at' => now()],
            OrderStatus::EXPIRED => ['expired_at' => now()],
            default => [],
        };

        $order->update([
            'status' => $toStatus,
            ...$timestamps,
        ]);

        $this->recordStatus($order, $fromStatus, $toStatus, $reason, $metadata);
    }

    private function recordStatus(
        Order $order,
        ?OrderStatus $fromStatus,
        OrderStatus $toStatus,
        ?string $reason = null,
        ?array $metadata = null,
    ): void {
        $order->statusHistories()->create([
            'from_status' => $fromStatus?->value,
            'to_status' => $toStatus->value,
            'reason' => $reason,
            'metadata' => $metadata,
        ]);
    }

    private function decimal(float $value, int $precision): string
    {
        return number_format(round($value, $precision), $precision, '.', '');
    }
}
