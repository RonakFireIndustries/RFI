<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Handle user login and token generation.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error('Invalid credentials', [], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->load(['roles', 'employee']);
        $user->setRelation('permissions', $user->roles->flatMap->permissions->unique('id'));

        return $this->success('Login successful', [
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Handle user logout by revoking the token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success('Logged out successfully');
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'employee']);
        $user->setRelation('permissions', $user->roles->flatMap->permissions->unique('id'));

        return $this->success('User fetched successfully', [
            'user' => $user
        ]);
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('Current password is incorrect', [], 422);
        }

        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return $this->success('Password changed successfully');
    }
}
