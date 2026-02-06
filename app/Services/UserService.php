<?php

namespace App\Services;

use App\Interfaces\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUsers(int $perPage)
    {
        return $this->userRepository->getAll($perPage);
    }

    public function getUserById(string $userId)
    {
        return $this->userRepository->findById($userId);
    }

    public function deleteUserAccount(string $userId)
    {
        return DB::transaction(function () use ($userId) {
            $user = $this->userRepository->findById($userId);

            if ($user->shop) {
                $user->shop()->delete();
            }

            $user->orders()->delete();
            $user->shopReviews()->delete();
            $user->productReviews()->delete();
            $user->voucherUsages()->delete();
            $user->orderStatusActions()->delete();
            $user->wishlists()->delete();
            $user->carts()->delete();
            $user->addresses()->delete();

            // cancel all login token
            $user->tokens()->delete();

            return $this->userRepository->delete($userId);
        });
    }
}
