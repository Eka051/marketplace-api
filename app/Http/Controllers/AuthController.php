<?php

namespace App\Http\Controllers;

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
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah'], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        
        if ($request->has('device_name')) {
            $user->tokens()->where('name', $request->device_name)->delete();
            
            $token = $user->createToken($request->device_name)->plainTextToken;
        } else {
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
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:user,seller'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        
        $token = $user->createToken('initial_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'user' => $user->only('user_id', 'name', 'email', 'profile_picture'),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil'], 200);
    }
}
