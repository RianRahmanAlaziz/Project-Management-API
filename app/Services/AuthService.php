<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthService
{
    /**
     * Mendaftarkan user dan membuat access token.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $token = $user
                ->createToken($data['device_name'] ?? 'web')
                ->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }

    /**
     * Memeriksa kredensial dan membuat access token.
     *
     * @param  array<string, mixed>  $data
     * @return array{user: User, token: string}
     *
     * @throws ValidationException
     */
    public function login(array $data): array
    {
        $user = User::query()
            ->where('email', $data['email'])
            ->first();

        if (
            $user === null ||
            ! Hash::check($data['password'], $user->password)
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Email atau password tidak sesuai.',
                ],
            ]);
        }

        /*
         * Memperbarui hash jika konfigurasi algoritma password berubah.
         */
        if (Hash::needsRehash($user->password)) {
            $user->forceFill([
                'password' => Hash::make($data['password']),
            ])->save();
        }

        $token = $user
            ->createToken($data['device_name'] ?? 'web')
            ->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Menghapus token yang sedang digunakan.
     */
    public function logout(User $user): void
    {
        $accessToken = $user->currentAccessToken();

        if ($accessToken !== null) {
            $accessToken->delete();
        }
    }
}
