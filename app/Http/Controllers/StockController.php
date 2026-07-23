<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockResource;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockController extends Controller
{
    public function index(Shop $shop): AnonymousResourceCollection
    {
        $warehouse = $shop->warehouse()->firstOrFail();
        $stocks = $warehouse->stocks()
            ->with(['warehouse.shop', 'productSku.product'])
            ->orderBy('product_sku_id')
            ->paginate(20);

        return StockResource::collection($stocks);
    }
}
