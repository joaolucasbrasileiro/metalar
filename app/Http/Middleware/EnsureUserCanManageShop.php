<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageShop
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();
        $shop = $request->route('shop');

        if (! $shop instanceof Shop) {
            $shop = Shop::where('code', $shop)->firstOrFail();
            $request->route()->setParameter('shop', $shop);
        }

        $canManage = $user
            && ($user->role === UserRole::ADMIN
                || ($user->role === UserRole::MODERATOR
                    && $user->shops()->whereKey($shop->id)->exists()));

        abort_unless(
            $canManage,
            403,
            'Voce nao tem permissao para administrar esta loja.',
        );

        return $next($request);
    }
}
