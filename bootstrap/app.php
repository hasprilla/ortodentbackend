<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Opción 1: Solo en prepend (se ejecuta antes)
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\BlockArtisanAccess::class,
        ]);

        // Opción 2: Solo en el grupo api (se ejecuta en el orden definido)
        $middleware->group('api', [
            \App\Http\Middleware\BlockArtisanAccess::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':100,1',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SanitizeInput::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'checkresource' => \App\Http\Middleware\CheckResourceExistence::class,
            'apiauth' => \App\Http\Middleware\ApiAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // 1. Configuración de reporte de excepciones
        $exceptions->report(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }

            if ($e instanceof InvalidSignatureException) {
                logger()->warning('Intento de acceso con firma inválida', [
                    'ip' => request()->ip(),
                    'url' => request()->fullUrl(),
                    'userAgent' => request()->userAgent()
                ]);
            }
        });

        // 2. Manejador principal de excepciones
        $exceptions->renderable(function (Throwable $e, Request $request) {
            $response = [
                'success' => false,
                'message' => app()->environment('local')
                    ? $e->getMessage()
                    : getGenericMessageForStatus(determineStatusCode($e)),
            ];

            if (app()->environment('local')) {
                $response['exception'] = get_class($e);
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, determineStatusCode($e));
        });

        // 3. Excepciones que no deben ser reportadas
        $exceptions->dontReport([
            AuthenticationException::class,
            AuthorizationException::class,
            ModelNotFoundException::class,
            ValidationException::class,
            TokenMismatchException::class,
            InvalidSignatureException::class,
            NotFoundHttpException::class,
        ]);
    })
    ->create();

// Función auxiliar para determinar el código de estado
function determineStatusCode(Throwable $e): int
{
    return match (true) {
        $e instanceof HttpException => $e->getStatusCode(),
        $e instanceof ModelNotFoundException => 404,
        $e instanceof AuthenticationException => 401,
        $e instanceof AuthorizationException => 403,
        $e instanceof ValidationException => 422,
        $e instanceof ThrottleRequestsException => 429,
        $e instanceof TokenMismatchException => 419,
        $e instanceof InvalidSignatureException => 403,
        default => 500,
    };
}

// Función auxiliar para mensajes genéricos
function getGenericMessageForStatus(int $status): string
{
    return match ($status) {
        400 => 'Solicitud incorrecta',
        401 => 'No autenticado',
        403 => 'No autorizado',
        404 => 'Recurso no encontrado',
        419 => 'Token CSRF inválido',
        422 => 'Error de validación',
        429 => 'Demasiadas solicitudes',
        500 => 'Error interno del servidor',
        default => 'Error inesperado',
    };
}
