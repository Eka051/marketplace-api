<?php

namespace App\Services;

use App\Interfaces\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data)
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepo->create($data);
    }

    public function login(string $email, string $password, string $deviceName = 'default')
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            return null;
        }

        $user = $this->userRepo->findByEmail($email);

        $this->userRepo->deleteTokens($user, $deviceName);

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout($user)
    {
        $user->currentAccessToken()->delete();
    }

    public function createToken($user, string $tokenName)
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
}
