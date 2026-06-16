<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Invalid credentials', 'data' => null], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $roles = $user->getRoleNames();
        $permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'success' => true,
            'message' => 'Authenticated',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load('branch', 'employee'),
                'roles' => $roles,
                'permissions' => $permissions
            ]
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json(['success' => true, 'message' => 'Success', 'data' => [
            'user' => $user->load('branch', 'employee'),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Successfully logged out', 'data' => null]);
    }
}
