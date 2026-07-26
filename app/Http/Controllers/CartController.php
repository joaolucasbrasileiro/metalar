<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function show(CartService $service): CartResource
    {
        return new CartResource($service->activeCart(Auth::guard('api')->user()));
    }

    public function storeItem(StoreCartItemRequest $request, CartService $service): CartResource
    {
        return new CartResource(
            $service->addItem(Auth::guard('api')->user(), $request->validated())
        );
    }

    public function updateItem(
        UpdateCartItemRequest $request,
        CartItem $cartItem,
        CartService $service,
    ): CartResource {
        return new CartResource(
            $service->updateItem(
                Auth::guard('api')->user(),
                $cartItem,
                (float) $request->validated('quantity'),
            )
        );
    }

    public function destroyItem(CartItem $cartItem, CartService $service): CartResource
    {
        return new CartResource(
            $service->removeItem(Auth::guard('api')->user(), $cartItem)
        );
    }

    public function clear(CartService $service): CartResource
    {
        return new CartResource($service->clear(Auth::guard('api')->user()));
    }
}
