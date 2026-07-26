<?php

namespace App\Services;

use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPromotion;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockReservationService
{
    public function reserve(
        Shop $shop,
        ProductSku $productSku,
        float $quantity,
        ?User $user = null,
        ?ShopSkuPromotion $promotion = null,
        string $reason = 'Reserva de estoque',
        ?Model $reference = null,
    ): Stock {
        $this->validatePositiveQuantity($quantity);

        return DB::transaction(function () use (
            $shop,
            $productSku,
            $quantity,
            $user,
            $promotion,
            $reason,
            $reference,
        ): Stock {
            $stock = $this->lockStock($shop, $productSku);
            $lockedPromotion = $this->lockPromotion($promotion, $shop, $productSku);

            $quantityOnHandBefore = (float) $stock->quantity_on_hand;
            $quantityReservedBefore = (float) $stock->quantity_reserved;
            $available = $quantityOnHandBefore - $quantityReservedBefore;

            if ($quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade solicitada supera o estoque disponivel.',
                ]);
            }

            if ($lockedPromotion && ! $lockedPromotion->isActive()) {
                throw ValidationException::withMessages([
                    'promotion' => 'A promocao nao esta ativa.',
                ]);
            }

            if ($lockedPromotion && $quantity > $lockedPromotion->remainingQuantity()) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade solicitada supera o saldo promocional disponivel.',
                ]);
            }

            $quantityReservedAfter = round($quantityReservedBefore + $quantity, 3);

            $stock->update([
                'quantity_reserved' => $this->decimal($quantityReservedAfter, 3),
            ]);

            if ($lockedPromotion) {
                $lockedPromotion->update([
                    'quantity_reserved' => $this->decimal(
                        (float) $lockedPromotion->quantity_reserved + $quantity,
                        3,
                    ),
                ]);
            }

            $this->recordMovement(
                stock: $stock,
                type: 'reservation',
                quantity: $quantity,
                quantityBefore: $quantityOnHandBefore,
                quantityAfter: $quantityOnHandBefore,
                quantityReservedBefore: $quantityReservedBefore,
                quantityReservedAfter: $quantityReservedAfter,
                reason: $reason,
                user: $user,
                reference: $reference,
            );

            return $stock->refresh()->load(['warehouse.shop', 'productSku.product']);
        });
    }

    public function release(
        Shop $shop,
        ProductSku $productSku,
        float $quantity,
        ?User $user = null,
        ?ShopSkuPromotion $promotion = null,
        string $reason = 'Liberacao de reserva',
        ?Model $reference = null,
    ): Stock {
        $this->validatePositiveQuantity($quantity);

        return DB::transaction(function () use (
            $shop,
            $productSku,
            $quantity,
            $user,
            $promotion,
            $reason,
            $reference,
        ): Stock {
            $stock = $this->lockStock($shop, $productSku);
            $lockedPromotion = $this->lockPromotion($promotion, $shop, $productSku);

            $quantityOnHandBefore = (float) $stock->quantity_on_hand;
            $quantityReservedBefore = (float) $stock->quantity_reserved;

            if ($quantity > $quantityReservedBefore) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade liberada supera a reserva existente.',
                ]);
            }

            if ($lockedPromotion && $quantity > (float) $lockedPromotion->quantity_reserved) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade liberada supera a reserva promocional existente.',
                ]);
            }

            $quantityReservedAfter = round($quantityReservedBefore - $quantity, 3);

            $stock->update([
                'quantity_reserved' => $this->decimal($quantityReservedAfter, 3),
            ]);

            if ($lockedPromotion) {
                $lockedPromotion->update([
                    'quantity_reserved' => $this->decimal(
                        (float) $lockedPromotion->quantity_reserved - $quantity,
                        3,
                    ),
                ]);
            }

            $this->recordMovement(
                stock: $stock,
                type: 'reservation_release',
                quantity: -$quantity,
                quantityBefore: $quantityOnHandBefore,
                quantityAfter: $quantityOnHandBefore,
                quantityReservedBefore: $quantityReservedBefore,
                quantityReservedAfter: $quantityReservedAfter,
                reason: $reason,
                user: $user,
                reference: $reference,
            );

            return $stock->refresh()->load(['warehouse.shop', 'productSku.product']);
        });
    }

    public function commitSale(
        Shop $shop,
        ProductSku $productSku,
        float $quantity,
        ?User $user = null,
        ?ShopSkuPromotion $promotion = null,
        string $reason = 'Venda confirmada',
        ?Model $reference = null,
    ): Stock {
        $this->validatePositiveQuantity($quantity);

        return DB::transaction(function () use (
            $shop,
            $productSku,
            $quantity,
            $user,
            $promotion,
            $reason,
            $reference,
        ): Stock {
            $stock = $this->lockStock($shop, $productSku);
            $lockedPromotion = $this->lockPromotion($promotion, $shop, $productSku);

            $quantityOnHandBefore = (float) $stock->quantity_on_hand;
            $quantityReservedBefore = (float) $stock->quantity_reserved;

            if ($quantity > $quantityReservedBefore) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade vendida supera a reserva existente.',
                ]);
            }

            if ($quantity > $quantityOnHandBefore) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade vendida supera o estoque fisico existente.',
                ]);
            }

            if ($lockedPromotion && $quantity > (float) $lockedPromotion->quantity_reserved) {
                throw ValidationException::withMessages([
                    'quantity' => 'A quantidade vendida supera a reserva promocional existente.',
                ]);
            }

            $quantityOnHandAfter = round($quantityOnHandBefore - $quantity, 3);
            $quantityReservedAfter = round($quantityReservedBefore - $quantity, 3);

            $stock->update([
                'quantity_on_hand' => $this->decimal($quantityOnHandAfter, 3),
                'quantity_reserved' => $this->decimal($quantityReservedAfter, 3),
            ]);

            if ($lockedPromotion) {
                $lockedPromotion->update([
                    'quantity_reserved' => $this->decimal(
                        (float) $lockedPromotion->quantity_reserved - $quantity,
                        3,
                    ),
                    'quantity_sold' => $this->decimal(
                        (float) $lockedPromotion->quantity_sold + $quantity,
                        3,
                    ),
                ]);
            }

            $this->recordMovement(
                stock: $stock,
                type: 'sale',
                quantity: -$quantity,
                quantityBefore: $quantityOnHandBefore,
                quantityAfter: $quantityOnHandAfter,
                quantityReservedBefore: $quantityReservedBefore,
                quantityReservedAfter: $quantityReservedAfter,
                reason: $reason,
                user: $user,
                reference: $reference,
            );

            return $stock->refresh()->load(['warehouse.shop', 'productSku.product']);
        });
    }

    private function lockStock(Shop $shop, ProductSku $productSku): Stock
    {
        $warehouse = $shop->warehouse()->firstOrFail();

        $stockId = Stock::firstOrCreate([
            'warehouse_id' => $warehouse->id,
            'product_sku_id' => $productSku->id,
        ])->id;

        return Stock::query()->lockForUpdate()->findOrFail($stockId);
    }

    private function lockPromotion(
        ?ShopSkuPromotion $promotion,
        Shop $shop,
        ProductSku $productSku,
    ): ?ShopSkuPromotion {
        if (! $promotion) {
            return null;
        }

        $lockedPromotion = ShopSkuPromotion::query()
            ->with('shopSkuPrice')
            ->lockForUpdate()
            ->findOrFail($promotion->id);

        if (
            $lockedPromotion->shopSkuPrice->shop_id !== $shop->id
            || $lockedPromotion->shopSkuPrice->product_sku_id !== $productSku->id
        ) {
            throw ValidationException::withMessages([
                'promotion' => 'A promocao nao pertence ao SKU desta loja.',
            ]);
        }

        return $lockedPromotion;
    }

    private function recordMovement(
        Stock $stock,
        string $type,
        float $quantity,
        float $quantityBefore,
        float $quantityAfter,
        float $quantityReservedBefore,
        float $quantityReservedAfter,
        string $reason,
        ?User $user = null,
        ?Model $reference = null,
    ): void {
        $stock->movements()->create([
            'user_id' => $user?->id,
            'type' => $type,
            'quantity' => $this->decimal($quantity, 3),
            'quantity_before' => $this->decimal($quantityBefore, 3),
            'quantity_after' => $this->decimal($quantityAfter, 3),
            'quantity_reserved_before' => $this->decimal($quantityReservedBefore, 3),
            'quantity_reserved_after' => $this->decimal($quantityReservedAfter, 3),
            'reason' => $reason,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
        ]);
    }

    private function validatePositiveQuantity(float $quantity): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'A quantidade deve ser maior que zero.',
            ]);
        }
    }

    private function decimal(float $value, int $precision): string
    {
        return number_format(round($value, $precision), $precision, '.', '');
    }
}
