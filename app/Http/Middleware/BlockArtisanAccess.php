<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockArtisanAccess
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('artisan*') || $request->is('*/artisan*')) {
            return response()->json(['error' => 'Access forbidden'], 403);
        }

        return $next($request);
    }
}
