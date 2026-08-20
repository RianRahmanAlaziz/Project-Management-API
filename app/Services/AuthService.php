<?php

namespace App\Services;

use App\Enums\UserRole;
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
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            return User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => UserRole::USER,
                'password' => Hash::make($data['password']),
            ]);
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

    public function login(array $data): User
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

        if (Hash::needsRehash($user->password)) {
            $user->forceFill([
                'password' => Hash::make($data['password']),
            ])->save();
        }

        return $user;
    }
}
