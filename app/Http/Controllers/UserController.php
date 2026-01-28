<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
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
            UserResource::class,
            'List users retrieved successfully'
        );
    }

    public function destroy(string $id)
    {
        $this->userService->deleteUserAccount($id);

        return $this->successResponse(null, 'User and all associated data deleted successfully');
    }
}
