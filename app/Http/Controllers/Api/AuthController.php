<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $deviceName = $validated['device_name'] ?? 'default';

        $result = $this->authService->login(
            $validated['email'],
            $validated['password'],
            $deviceName
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => $result['user']->only('name', 'email', 'role', 'profile_picture'),
            'access_token' => $result['token'],
            'token_type' => 'Bearer',
        ], status: 200);
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user->only('name', 'email', 'profile_picture'),
        ], status: 201);
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => 'Logout berhasil'], status: 200);
    }

    public function createToken(Request $request)
    {
        $token = $this->authService->createToken(
            $request->user(),
            $request->token_name
        );

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken
        ]);
    }
}
