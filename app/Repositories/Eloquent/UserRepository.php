<?php

namespace App\Repositories\Eloquent;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;

class UserRepository implements UserRepositoryInterface {
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function getAll(int $perPage = 10)
    {
        return User::latest()->paginate($perPage);
    }

    public function findById(string $userId)
    {
        return User::with(['shop', 'addresses'])->findOrFail($userId);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function delete(string $userId)
    {
        $user = User::findOrFail($userId);
        return $user->delete();
    }

    public function deleteTokens(object $user, string $deviceName)
    {
        return $user->tokens()->where('name', $deviceName)->delete();
    }
}