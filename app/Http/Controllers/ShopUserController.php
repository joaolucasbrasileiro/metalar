<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Response;

class ShopUserController extends Controller
{
    public function store(Shop $shop, User $user): Response
    {
        $shop->users()->syncWithoutDetaching([$user->id]);

        return response()->noContent();
    }

    public function destroy(Shop $shop, User $user): Response
    {
        $shop->users()->detach($user->id);

        return response()->noContent();
    }
}
