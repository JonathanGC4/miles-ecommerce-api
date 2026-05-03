<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsSeller
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->isAdmin() && ! $request->user()?->isSeller()) {
            return response()->json([
                'message' => 'Acceso restringido a sellers y administradores.',
            ], 403);
        }
        return $next($request);
    }
}
