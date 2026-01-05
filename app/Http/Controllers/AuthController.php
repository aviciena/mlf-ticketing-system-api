<?php

namespace App\Http\Controllers;

use App\Models\Tokens;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends BaseController
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || ! Hash::check($request->password, $user->password)) {
            $this->validateException([
                'Username atau Password tidak valid. Silakan periksa kembali dan coba lagi.',
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;
        $existingToken = Tokens::where('user_id', $user->id)->first();
        if ($existingToken) {
            Tokens::where('user_id', $user->id)->delete();

            $split = explode("|", $existingToken->access_token);
            PersonalAccessToken::where('id', (int)$split[0])->delete();
        }

        Tokens::create([
            'access_token' => $token,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'name' => $user->name,
                    'username' => $request->username,
                    'role' => [
                        'code' => $user->role->code,
                        'description' => $user->role->description
                    ],
                ]
            ]
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        Tokens::where('user_id', $request->user()->id)->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // Change Password
    public function changePassword(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Validate the current or old password is match or not
        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->sendError('Current password is incorrect.', [], 422);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }
}
