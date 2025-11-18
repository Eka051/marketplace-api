<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'device_name' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], status: 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah'
            ], status: 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        if ($request->has('device_name')) {
            $user->tokens()->where('name', $request->device_name)->delete();

            $token = $user->createToken($request->device_name)->plainTextToken;
        } else {
            $user->tokens()->where('name', 'default')->delete();
            $token = $user->createToken('default')->plainTextToken;
        }


        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'user' => [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_picture' => $user->profile_picture,
                'role' => $user->role,
            ],
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], status: 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string:min10',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:user,seller'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], status: 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user->only('user_id', 'name', 'email', 'role', 'profile_picture'),
        ], status: 201);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil'], status: 200);
    }

    public function createToken(Request $request) {
        $request->validate([
            'token_name' => 'required|string'
        ]);

        $token = $request->user()->createToken($request->token_name);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken
        ]);
    }
}
