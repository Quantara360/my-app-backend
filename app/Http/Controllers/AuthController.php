<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'username'     => ['required', 'string', 'max:255', 'unique:users,username', 'alpha_dash'],
            'email'        => ['required', 'email', 'unique:users,email', 'max:255'],
            'password'     => ['required', 'string', 'min:8'],
            'role'         => ['required', 'in:supervisor,officeStaff,admin'],
            'worksite_id'  => ['nullable', 'exists:worksites,id'],
            'hospital_ids' => ['nullable', 'array'],
            'hospital_ids.*' => ['integer', 'exists:hospitals,id'],
        ]);

        // If hospital_ids provided, verify all hospitals belong to the given worksite
        if (!empty($data['hospital_ids']) && !empty($data['worksite_id'])) {
            $invalidCount = \App\Models\Hospital::whereIn('id', $data['hospital_ids'])
                ->where('worksite_id', '!=', $data['worksite_id'])
                ->count();
            if ($invalidCount > 0) {
                return Response::json(['message' => 'Some hospitals do not belong to the selected main site.'], 422);
            }
        }

        $user = \App\Models\User::create([
            'name'        => $data['name'],
            'username'    => $data['username'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'role'        => $data['role'],
            'worksite_id' => $data['worksite_id'] ?? null,
        ]);

        // Sync hospital scopes (pivot)
        if (!empty($data['hospital_ids'])) {
            $user->hospitals()->sync($data['hospital_ids']);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return Response::json([
            'user'         => $this->formatUser($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        // Rate limit failed login attempts: 5 attempts per 15 minutes
        $throttleKey = 'login-attempts:' . $request->getClientIp();
        if (RateLimiter::tooManyAttempts($throttleKey, 5, 15)) {
            return Response::json([
                'message' => 'Too many login attempts. Please try again later.'
            ], 429);
        }

        $user = User::where('username', $data['username'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 15);
            return Response::json(['message' => 'Invalid credentials'], 401);
        }

        // Clear rate limiting on successful login
        RateLimiter::clear($throttleKey);

        // Revoke previous tokens for security (optional - logout user from other devices)
        // $user->tokens()->delete();

        $token = $user->createToken('api-token')->plainTextToken;

        return Response::json([
            'user'         => $this->formatUser($user),
            'access_token' => $token,
            'token_type'   => 'Bearer',
        ]);
    }

    public function me(Request $request)
    {
        return Response::json($this->formatUser($request->user()));
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return Response::json(['message' => 'Logged out successfully']);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'alpha_dash', 'unique:users,username,' . $user->id],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        if (isset($data['username'])) {
            $user->username = $data['username'];
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return Response::json([
            'message' => 'Profile updated successfully',
            'user'    => $this->formatUser($user)
        ]);
    }

    /**
     * Format a user for API responses — always includes scope fields.
     */
    private function formatUser(\App\Models\User $user): array
    {
        $user->loadMissing('hospitals');
        return [
            'id'           => $user->id,
            'name'         => $user->name,
            'username'     => $user->username,
            'email'        => $user->email,
            'role'         => $user->role,
            'worksite_id'  => $user->worksite_id,
            'hospital_ids' => $user->hospitals->pluck('id')->values()->all(),
        ];
    }
}

