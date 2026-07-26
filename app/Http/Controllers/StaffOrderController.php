<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Shop;
use App\Services\OrderService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StaffOrderController extends Controller
{
    public function index(Shop $shop): AnonymousResourceCollection
    {
        $orders = Order::query()
            ->whereHas('items', fn ($query) => $query->where('shop_id', $shop->id))
            ->with(['items.product', 'items.shop', 'paymentAttempts'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function show(Shop $shop, Order $order, OrderService $orderService): OrderResource
    {
        abort_unless(
            $order->items()->where('shop_id', $shop->id)->exists(),
            404,
        );

        return new OrderResource($order->load($orderService->relations()));
    }
}
