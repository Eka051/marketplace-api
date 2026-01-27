<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function destroy(string $id)
    {
        $this->userService->deleteUserAccount($id);

        return $this->successResponse(null, 'User and all associated data deleted successfully');
    }
}
