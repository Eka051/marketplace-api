<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserLoginResource;
use App\Http\Resources\UserRegisterResource;
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
            return $this->errorResponse(
                'Invalid email or password',
                401
            );
        }

        return $this->successResponse(
            [
                'user' => (new UserLoginResource($result['user']))->toArray(request()),
                'access_token' => $result['token'],
                'token_type' => 'Bearer',

            ],
            'Login successfully',
        );
    }

    public function register(RegisterRequest $request)
    {
        $user = $this->authService->register($request->validated());

        return $this->successResponse(
            [
                'user' => (new UserRegisterResource($user))->toArray(request()),
            ],
            'Registration successfully',
            201
        );
    }

    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->successResponse(
            null,
            'Logout successfully',
        );
    }

    public function createToken(Request $request)
    {
        $token = $this->authService->createToken(
            $request->user(),
            $request->token_name
        );

        return $this->successResponse(
            [
                'token' => $token->plainTextToken
            ],
        );
    }
}

// created by: Dian Eka R.
