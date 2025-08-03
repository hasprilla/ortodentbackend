<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Bloquear navegadores (opcional pero recomendado)
        if ($this->isBrowserRequest($request)) {
            Log::warning('Intento de acceso desde navegador bloqueado', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return $this->errorResponse('Browser access not allowed', Response::HTTP_FORBIDDEN);
        }

        // 2. Validar headers requeridos
        if (!$this->validateHeaders($request)) {
            return $this->errorResponse('Invalid headers', Response::HTTP_BAD_REQUEST);
        }

        // 3. Verificar token
        if (!$request->bearerToken()) {
            return $this->errorResponse('Authorization token missing', Response::HTTP_UNAUTHORIZED);
        }

        // 4. Validar token con manejo de errores
        try {
            if (!auth('sanctum')->check()) {
                Log::warning('Intento de acceso con token inválido', ['ip' => $request->ip()]);
                return $this->errorResponse('Invalid or expired token', Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            Log::error('Error validando token', ['error' => $e->getMessage()]);
            return $this->errorResponse('Token validation failed', Response::HTTP_UNAUTHORIZED);
        }

        // 5. Validar contenido para métodos POST/PUT/PATCH
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            if (!$request->isJson()) {
                return $this->errorResponse('Content-Type must be application/json', Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
            }
        }

        return $next($request);
    }

    private function isBrowserRequest(Request $request): bool
    {
        $userAgent = $request->header('User-Agent');
        if (!$userAgent) return false;

        $browserPatterns = [
            'Mozilla', 'Chrome', 'Safari', 'Firefox', 'Edge', 'Opera',
            'Trident', 'MSIE', 'Gecko', 'WebKit', 'AppleWebKit'
        ];

        foreach ($browserPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function validateHeaders(Request $request): bool
    {
        $requiredHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => $request->isMethod('GET') ? null : 'application/json'
        ];

        foreach ($requiredHeaders as $header => $value) {
            if ($value && !$request->headers->has($header)) {
                return false;
            }
        }

        return true;
    }

    private function errorResponse(string $message, int $statusCode): Response
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }
}