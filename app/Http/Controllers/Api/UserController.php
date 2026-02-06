<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserListResource;
use App\Http\Resources\UserProfileResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $users = $this->userService->getUsers($perPage);

        return $this->paginationResponse(
            $users,
            UserListResource::class,
            'User lists retrieved successfully'
        );
    }

    public function show(string $userId)
    {
        $user = $this->userService->getUserById($userId);

        return $this->successResponse(
            new UserListResource($user),
            'User details retrieved successfully'
        );
    }

    public function destroy(string $id)
    {
        $this->userService->deleteUserAccount($id);

        return $this->successResponse(null, 'User and all associated data deleted successfully');
    }

    public function profile(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser) {
            return $this->errorResponse('Unauthenticated', 401);
        }

        // $authUser->load('shop', 'addresses');

        $user = $this->userService->getUserById((string) $authUser->getKey());

        return $this->successResponse(
            new UserProfileResource($user),
            'User profile retrieved successfully'
        );
    }
}
