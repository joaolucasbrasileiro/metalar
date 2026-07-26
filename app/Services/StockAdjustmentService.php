<?php

namespace App\Services;

use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    public function adjust(
        Shop $shop,
        ProductSku $productSku,
        float $quantity,
        string $reason,
        User $user,
    ): Stock {
        $warehouse = $shop->warehouse()->firstOrFail();

        return DB::transaction(function () use (
            $warehouse,
            $productSku,
            $quantity,
            $reason,
            $user,
        ): Stock {
            $stockId = Stock::firstOrCreate([
                'warehouse_id' => $warehouse->id,
                'product_sku_id' => $productSku->id,
            ])->id;

            $stock = Stock::query()->lockForUpdate()->findOrFail($stockId);
            $before = (float) $stock->quantity_on_hand;
            $reservedBefore = (float) $stock->quantity_reserved;
            $after = round($before + $quantity, 3);

            if ($after < 0 || $after < (float) $stock->quantity_reserved) {
                throw ValidationException::withMessages([
                    'quantity' => 'O ajuste deixaria o estoque abaixo da quantidade reservada.',
                ]);
            }

            $stock->update([
                'quantity_on_hand' => number_format($after, 3, '.', ''),
            ]);

            $stock->movements()->create([
                'user_id' => $user->id,
                'type' => 'adjustment',
                'quantity' => number_format($quantity, 3, '.', ''),
                'quantity_before' => number_format($before, 3, '.', ''),
                'quantity_after' => number_format($after, 3, '.', ''),
                'quantity_reserved_before' => number_format($reservedBefore, 3, '.', ''),
                'quantity_reserved_after' => number_format($reservedBefore, 3, '.', ''),
                'reason' => $reason,
            ]);

            return $stock->load(['warehouse.shop', 'productSku.product']);
        });
    }
}
