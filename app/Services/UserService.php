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
            'role' =>  $data['role'],
        ]);
    }

    public function update(
        User $user,
        array $data,
    ): User {
        $isLastSuperAdmin =
            ! User::query()
                ->where('role', UserRole::SUPER_ADMIN)
                ->whereKeyNot($user->id)
                ->exists();
        if (
            $user->isSuperAdmin()
            && isset($data['role'])
            && $data['role'] === UserRole::USER
            && $isLastSuperAdmin
        ) {
            throw ValidationException::withMessages([
                'role' => [
                    'Tidak dapat mengubah role Super Admin terakhir.',
                ],
            ]);
        }

        $user->update(
            Arr::only(
                $data,
                [
                    'name',
                    'email',
                    'role',
                ],
            )
        );

        return $user->refresh();
    }

    public function resetPassword(User $user, string $password): User
    {
        $user->update([
            'password' => $password,
        ]);

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
