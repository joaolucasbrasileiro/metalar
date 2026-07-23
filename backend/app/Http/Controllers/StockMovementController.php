<?php

namespace App\Http\Controllers;

use App\Http\Resources\StockMovementResource;
use App\Models\Shop;
use App\Models\StockMovement;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StockMovementController extends Controller
{
    public function index(Shop $shop): AnonymousResourceCollection
    {
        $warehouse = $shop->warehouse()->firstOrFail();
        $movements = StockMovement::query()
            ->whereHas('stock', fn ($query) => $query
                ->where('warehouse_id', $warehouse->id))
            ->with(['user', 'stock.productSku'])
            ->latest()
            ->paginate(20);

        return StockMovementResource::collection($movements);
    }
}
