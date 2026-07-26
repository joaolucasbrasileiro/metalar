<?php

namespace Tests\Feature\Commerce;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Models\ShopSkuPrice;
use App\Models\ShopSkuPromotion;
use App\Models\Stock;
use App\Models\User;
use App\Services\StockReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StockReservationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_reserves_releases_and_commits_reserved_stock_as_a_sale(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $price = $this->createOffer($shop, $sku, 10, 50);
        $promotion = $this->createPromotion($price, 40, 5);
        $user = User::factory()->create(['role' => UserRole::COMMON]);
        $service = app(StockReservationService::class);

        $service->reserve($shop, $sku, 3, $user, $promotion);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '10.000',
            'quantity_reserved' => '3.000',
        ]);
        $this->assertDatabaseHas('shop_sku_promotions', [
            'id' => $promotion->id,
            'quantity_reserved' => '3.000',
            'quantity_sold' => '0.000',
        ]);

        $service->release($shop, $sku, 1, $user, $promotion);
        $service->commitSale($shop, $sku, 2, $user, $promotion);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '8.000',
            'quantity_reserved' => '0.000',
        ]);
        $this->assertDatabaseHas('shop_sku_promotions', [
            'id' => $promotion->id,
            'quantity_reserved' => '0.000',
            'quantity_sold' => '2.000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'user_id' => $user->id,
            'type' => 'reservation',
            'quantity' => '3.000',
            'quantity_before' => '10.000',
            'quantity_after' => '10.000',
            'quantity_reserved_before' => '0.000',
            'quantity_reserved_after' => '3.000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'user_id' => $user->id,
            'type' => 'reservation_release',
            'quantity' => '-1.000',
            'quantity_reserved_before' => '3.000',
            'quantity_reserved_after' => '2.000',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'user_id' => $user->id,
            'type' => 'sale',
            'quantity' => '-2.000',
            'quantity_before' => '10.000',
            'quantity_after' => '8.000',
            'quantity_reserved_before' => '2.000',
            'quantity_reserved_after' => '0.000',
        ]);
    }

    public function test_it_rejects_reservations_above_available_stock(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 2, 50);

        $this->expectException(ValidationException::class);

        app(StockReservationService::class)->reserve($shop, $sku, 3);

        $this->assertDatabaseHas('stocks', [
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => '2.000',
            'quantity_reserved' => '0.000',
        ]);
    }

    public function test_it_rejects_sale_commit_without_existing_reservation(): void
    {
        $shop = $this->createShop('matriz');
        $sku = $this->createSku();
        $this->createOffer($shop, $sku, 5, 50);

        $this->expectException(ValidationException::class);

        app(StockReservationService::class)->commitSale($shop, $sku, 1);
    }

    private function createProduct(string $name = 'Produto', string $slug = 'produto'): Product
    {
        return Product::create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    private function createSku(
        ?Product $product = null,
        string $sku = 'SKU-001',
    ): ProductSku {
        return ProductSku::create([
            'product_id' => ($product ?? $this->createProduct())->id,
            'sku' => $sku,
            'unit' => 'un',
            'transfer_batch_quantity' => 1,
            'transfer_fee_per_batch' => 0,
        ]);
    }

    private function createShop(string $code): Shop
    {
        $number = $code === 'matriz' ? '1' : '2';

        $shop = Shop::create([
            'code' => $code,
            'name' => ucfirst($code),
            'cnpj' => str_pad($number, 14, '0', STR_PAD_LEFT),
            'phone' => '7199999999'.$number,
            'zip_code' => '46430000',
            'street' => 'Rua principal',
            'number' => $number,
            'neighborhood' => 'Centro',
            'city' => 'Guanambi',
            'state' => 'BA',
        ]);

        $shop->warehouse()->create(['name' => "Warehouse {$code}"]);

        return $shop;
    }

    private function createOffer(
        Shop $shop,
        ProductSku $sku,
        float $quantity,
        float $price,
    ): ShopSkuPrice {
        Stock::create([
            'warehouse_id' => $shop->warehouse->id,
            'product_sku_id' => $sku->id,
            'quantity_on_hand' => $quantity,
        ]);

        return ShopSkuPrice::create([
            'shop_id' => $shop->id,
            'product_sku_id' => $sku->id,
            'price' => $price,
        ]);
    }

    private function createPromotion(
        ShopSkuPrice $price,
        float $promotionalPrice,
        float $quantity,
    ): ShopSkuPromotion {
        return ShopSkuPromotion::create([
            'shop_sku_price_id' => $price->id,
            'created_by_user_id' => User::factory()->create([
                'role' => UserRole::ADMIN,
            ])->id,
            'promotional_price' => $promotionalPrice,
            'quantity_limit' => $quantity,
            'starts_at' => now()->subMinute(),
        ]);
    }
}
