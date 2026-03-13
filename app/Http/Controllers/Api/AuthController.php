<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse{
        $validated = $request->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'success'=>true,
            'data'=>[
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'compte créé avec succés',
        ], 201);
    }
    public function login(LoginRequest $request): JsonResponse{
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();
    if(!$user ||!Hash::check($validated['password'], $user->password)){
        return response()->json([
            'success' => false,
            'errors' =>[
                'credentials' => ['Email ou mot de passe incorrect'],
            ],
            'message' => 'Authentification échouée',
        ], 401);
    }
    $token = $user->createToken('auth_token')->plainTextToken;
    return response()->json([
        'success' => true,
        'data' =>[
            'user' => $user,
            'token' => $token,
        ],
        'message' => 'connxion réusie',
    ], 200);
    }
    public function me(Request $request): JsonResponse{
        return response()->json([
            'success' => true,
            'data' => $request->user(),
            'message' => 'Utilisateur connecté récupéré', 
        ],200);
    }
    public function logout(Request  $request): JsonResponse{
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'success' => true,
            'data' => null,
            'message' => 'deconnexion réussie',
        ], 200);
    }
}
