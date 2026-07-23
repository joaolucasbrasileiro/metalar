<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Http\Resources\StockResource;
use App\Models\ProductSku;
use App\Models\Shop;
use App\Services\StockAdjustmentService;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function store(
        AdjustStockRequest $request,
        Shop $shop,
        ProductSku $productSku,
        StockAdjustmentService $service,
    ): StockResource {
        $stock = $service->adjust(
            $shop,
            $productSku,
            (float) $request->validated('quantity'),
            $request->validated('reason'),
            Auth::guard('api')->user(),
        );

        return new StockResource($stock);
    }
}
