<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

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

    public function staffIndex(): AnonymousResourceCollection
    {
        $user = Auth::guard('api')->user();

        abort_unless(
            $user && in_array($user->role, [UserRole::MODERATOR, UserRole::ADMIN], true),
            403,
            'Voce nao tem permissao para administrar lojas.',
        );

        $shops = $user->role === UserRole::ADMIN
            ? Shop::query()
                ->with('warehouse')
                ->orderBy('name')
                ->get()
            : $user->shops()
                ->with('warehouse')
                ->orderBy('name')
                ->get();

        return ShopResource::collection($shops);
    }
}
