<?php

namespace App\Http\Controllers;

use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShopController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $shops = Shop::with('warehouse')->get();

        return ShopResource::collection($shops);
    }

    public function show(Shop $shop): ShopResource
    {
        $shop->load('warehouse');

        return new ShopResource($shop);
    }
}
