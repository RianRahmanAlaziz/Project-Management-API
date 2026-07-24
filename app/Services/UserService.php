<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function paginate(
        ?string $search = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return User::query()
            ->when(
                $search,
                function ($query, $search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => UserRole::USER,
        ]);
    }

    public function update(
        User $user,
        array $data
    ): User {
        $user->update(
            Arr::only(
                $data,
                [
                    'name',
                    'email',
                    'password',
                ],
            )
        );

        return $user->refresh();
    }

    public function delete(User $user): void
    {
        if ($user->ownedWorkspaces()->exists()) {
            throw ValidationException::withMessages([
                'user' => [
                    'User tidak dapat dihapus karena masih menjadi owner workspace.',
                ],
            ]);
        }

        $user->delete();
    }
}
