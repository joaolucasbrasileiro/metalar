<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $orders = Auth::guard('api')->user()
            ->orders()
            ->with(['items.product', 'items.shop', 'paymentAttempts'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function store(
        CartService $cartService,
        OrderService $orderService,
    ): JsonResponse {
        $user = Auth::guard('api')->user();
        $order = $orderService->createFromCart(
            $user,
            $cartService->activeCart($user),
        );

        return (new OrderResource($order->refresh()->load($orderService->relations())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Order $order, OrderService $orderService): OrderResource
    {
        $this->authorizeOwner($order);

        return new OrderResource($order->load($orderService->relations()));
    }

    public function cancel(Order $order, OrderService $orderService): OrderResource
    {
        $this->authorizeOwner($order);

        return new OrderResource(
            $orderService->cancel($order, Auth::guard('api')->user())
        );
    }

    private function authorizeOwner(Order $order): void
    {
        abort_unless(
            $order->user_id === Auth::guard('api')->id(),
            404,
        );
    }
}
