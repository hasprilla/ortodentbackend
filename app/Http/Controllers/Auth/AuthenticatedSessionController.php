<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Models\User;

class AuthenticatedSessionController extends Controller
{


    public function store(LoginRequest $request): JsonResponse
    {
        // Validar que vengan los campos necesarios
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'password' => ['required'],
        ]);

        // Verificar si el usuario existe
        $user = User::where('email', $request['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'An error occurred during login. Please check your credentials.',
            ], 404);
        }

        // Verificar credenciales del usuario
        $request->authenticate();

        // Generar un token usando Sanctum
        $token = $request->user()->createToken('Postman Token')->plainTextToken;

        return response()->json([
            'data' => $user,
            'token' => $token,
        ], 200);
    }

    
    // public function store(LoginRequest $request): JsonResponse
    // {

    //      $request->validate([
    //         'name' => ['required', 'string', 'max:255'],
    //         'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class]
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'An error occurred during login. Please check your credentials.',
    //         ], 404);
    //     }

    //     $request->authenticate();

    //     $token = $request->user()->createToken('Token de Postman')->plainTextToken;

    //     return response()->json([
    //         'data' => $user,
    //         'token' => $token,
    //     ], 200);
    // }

  
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
