<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password tidak valid.'],
            ]);
        }

        $deviceName = $credentials['device_name'] ?? 'default-device';

        return response()->json($this->issueTokenPair($user, $deviceName), 200);
    }

    public function refresh(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'refresh_token' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $refreshToken = PersonalAccessToken::findToken($payload['refresh_token']);

        if (! $refreshToken || ! $refreshToken->can('refresh')) {
            return response()->json([
                'message' => 'Refresh token tidak valid.',
            ], 401);
        }

        if ($refreshToken->expires_at && $refreshToken->expires_at->isPast()) {
            $refreshToken->delete();

            return response()->json([
                'message' => 'Refresh token sudah kedaluwarsa.',
            ], 401);
        }

        $user = $refreshToken->tokenable;

        if (! $user instanceof User) {
            $refreshToken->delete();

            return response()->json([
                'message' => 'Refresh token tidak terhubung ke user yang valid.',
            ], 401);
        }

        $sessionKey = $this->extractSessionKey($refreshToken->name);
        $deviceName = $payload['device_name'] ?? $this->extractDeviceName($refreshToken->name);

        $this->revokeSessionTokens($user, $sessionKey);

        return response()->json($this->issueTokenPair($user, $deviceName), 200);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->loadMissing('roles', 'permissions');

        return response()->json([
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $currentToken = $user?->currentAccessToken();

        if ($user && $currentToken) {
            $this->revokeSessionTokens($user, $this->extractSessionKey($currentToken->name));
        }

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function issueTokenPair(User $user, string $deviceName): array
    {
        $sessionKey = bin2hex(random_bytes(20));
        $accessExpiresAt = now()->addMinutes((int) env('SANCTUM_ACCESS_TOKEN_TTL', 15));
        $refreshExpiresAt = now()->addDays((int) env('SANCTUM_REFRESH_TOKEN_TTL_DAYS', 7));

        $accessToken = $user->createToken(
            "access:{$sessionKey}:{$deviceName}",
            ['access'],
            $accessExpiresAt
        )->plainTextToken;

        $refreshToken = $user->createToken(
            "refresh:{$sessionKey}:{$deviceName}",
            ['refresh'],
            $refreshExpiresAt
        )->plainTextToken;

        return [
            'token_type' => 'Bearer',
            'access_token' => $accessToken,
            'access_token_expires_at' => $accessExpiresAt->toIso8601String(),
            'refresh_token' => $refreshToken,
            'refresh_token_expires_at' => $refreshExpiresAt->toIso8601String(),
            'user' => $user->only(['id', 'name', 'email']),
        ];
    }

    private function revokeSessionTokens(User $user, string $sessionKey): void
    {
        $user->tokens()
            ->where(function ($query) use ($sessionKey) {
                $query->where('name', 'like', "access:{$sessionKey}:%")
                    ->orWhere('name', 'like', "refresh:{$sessionKey}:%");
            })
            ->delete();
    }

    private function extractSessionKey(string $tokenName): string
    {
        $parts = explode(':', $tokenName, 3);

        return $parts[1] ?? '';
    }

    private function extractDeviceName(string $tokenName): string
    {
        $parts = explode(':', $tokenName, 3);

        return $parts[2] ?? 'default-device';
    }
}
