<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Route;

class CheckResourceExistence
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si la ruta solicitada existe
        $routeExists = Route::has($request->route()->getName());

        // Si la ruta no existe, devolver un error 404
        if (!$routeExists) {
            return response()->json([
                'message' => 'Resource not found'
            ], 404);
        }

        // Si la ruta existe, proceder con la solicitud
        return $next($request);
    }
}


