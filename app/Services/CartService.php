<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPromotion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function activeCart(User $user, bool $create = true): Cart
    {
        $cart = $user->carts()
            ->where('status', 'active')
            ->latest()
            ->first();

        if (! $cart && $create) {
            $cart = $user->carts()->create(['status' => 'active']);
        }

        return ($cart ?? new Cart(['status' => 'active']))
            ->load($this->relations());
    }

    public function addItem(User $user, array $data): Cart
    {
        return DB::transaction(function () use ($user, $data): Cart {
            $cart = $this->activeCart($user);
            $quantity = round((float) $data['quantity'], 3);
            $productSku = ProductSku::query()->findOrFail($data['product_sku_id']);
            $shop = Shop::query()->findOrFail($data['shop_id']);
            $promotion = $this->validateSelection(
                shop: $shop,
                productSku: $productSku,
                promotionId: $data['promotion_id'] ?? null,
            );

            $itemQuery = $cart->items()
                ->where('product_sku_id', $productSku->id)
                ->where('shop_id', $shop->id);

            $promotion
                ? $itemQuery->where('shop_sku_promotion_id', $promotion->id)
                : $itemQuery->whereNull('shop_sku_promotion_id');

            $cartItem = $itemQuery->lockForUpdate()->first();

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $this->decimal((float) $cartItem->quantity + $quantity, 3),
                ]);
            } else {
                $cart->items()->create([
                    'product_sku_id' => $productSku->id,
                    'shop_id' => $shop->id,
                    'shop_sku_promotion_id' => $promotion?->id,
                    'quantity' => $this->decimal($quantity, 3),
                ]);
            }

            return $cart->refresh()->load($this->relations());
        });
    }

    public function updateItem(User $user, CartItem $cartItem, float $quantity): Cart
    {
        return DB::transaction(function () use ($user, $cartItem, $quantity): Cart {
            $lockedItem = $this->lockOwnedItem($user, $cartItem);

            $lockedItem->update([
                'quantity' => $this->decimal($quantity, 3),
            ]);

            return $lockedItem->cart->refresh()->load($this->relations());
        });
    }

    public function removeItem(User $user, CartItem $cartItem): Cart
    {
        return DB::transaction(function () use ($user, $cartItem): Cart {
            $lockedItem = $this->lockOwnedItem($user, $cartItem);
            $cart = $lockedItem->cart;

            $lockedItem->delete();

            return $cart->refresh()->load($this->relations());
        });
    }

    public function clear(User $user): Cart
    {
        return DB::transaction(function () use ($user): Cart {
            $cart = $this->activeCart($user);
            $cart->items()->delete();

            return $cart->refresh()->load($this->relations());
        });
    }

    public function markConverted(Cart $cart, int $orderId): void
    {
        $cart->update([
            'status' => 'converted',
            'converted_order_id' => $orderId,
        ]);
    }

    public function relations(): array
    {
        return [
            'items.productSku.product',
            'items.shop',
            'items.promotion',
        ];
    }

    private function validateSelection(
        Shop $shop,
        ProductSku $productSku,
        ?int $promotionId = null,
    ): ?ShopSkuPromotion {
        $price = $shop->skuPrices()
            ->where('product_sku_id', $productSku->id)
            ->first();

        if (! $price) {
            throw ValidationException::withMessages([
                'product_sku_id' => 'Nao ha preco cadastrado para este SKU nesta loja.',
            ]);
        }

        if (! $promotionId) {
            return null;
        }

        $promotion = $price->promotions()
            ->whereKey($promotionId)
            ->first();

        if (! $promotion || ! $promotion->isActive()) {
            throw ValidationException::withMessages([
                'promotion_id' => 'A promocao escolhida nao esta disponivel para este SKU nesta loja.',
            ]);
        }

        return $promotion;
    }

    private function lockOwnedItem(User $user, CartItem $cartItem): CartItem
    {
        $lockedItem = CartItem::query()
            ->with('cart')
            ->whereKey($cartItem->id)
            ->lockForUpdate()
            ->firstOrFail();

        abort_unless(
            $lockedItem->cart->user_id === $user->id
                && $lockedItem->cart->status === 'active',
            404,
        );

        return $lockedItem;
    }

    private function decimal(float $value, int $precision): string
    {
        return number_format(round($value, $precision), $precision, '.', '');
    }
}
