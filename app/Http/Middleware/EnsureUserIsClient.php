<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()?->isClient()) {
            return response()->json([
                'message' => 'Acceso restringido a clientes.',
            ], 403);
        }
        return $next($request);
    }
}
