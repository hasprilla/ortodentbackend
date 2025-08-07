<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\Activity;

class AuthenticatedSessionController extends Controller
{
    public function store(LoginRequest $request): JsonResponse
    {
        // 1. Rate limiting
        $this->ensureIsNotRateLimited($request);

        // 2. Autenticación (usando el facade Auth)
        if (!Auth::attempt($request->only('email', 'password'), $request->filled('remember'))) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        // 3. Generar token con expiración
        $token = $request->user()->createToken(
            'api-token',
            ['*'],
            now()->addHours(2)
        )->plainTextToken;



        // Registrar la actividad
        activity()
            ->causedBy($request->user())
            ->withProperties([
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ])
            ->log('Successful API login');


        return response()->json([
            'success' => true,
            'token' => $token,
            'data' => $this->sanitizeUser($request->user())
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        // Descomenta si instalas activitylog:

        activity()
            ->causedBy($request->user())
            ->log('API logout');


        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out'
        ]);
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }

    private function sanitizeUser($user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }
}
