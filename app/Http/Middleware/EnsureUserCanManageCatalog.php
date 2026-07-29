<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageCatalog
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('api')->user();

        abort_unless(
            $user && in_array($user->role, [UserRole::MODERATOR, UserRole::ADMIN], true),
            403,
            'Voce nao tem permissao para gerenciar o catalogo.',
        );

        return $next($request);
    }
}
