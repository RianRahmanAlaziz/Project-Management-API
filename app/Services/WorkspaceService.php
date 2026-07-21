<?php

namespace App\Services;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class WorkspaceService
{
    /**
     * Mengambil workspace yang diikuti user.
     */
    public function paginateForUser(
        User $user,
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return Workspace::query()
            ->whereHas(
                'memberships',
                fn($query) => $query->where(
                    'user_id',
                    $user->id,
                ),
            )
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%",
                                );
                        }
                    );
                },
            )
            ->with([
                'owner',
                'memberships' => fn($query) => $query
                    ->where('user_id', $user->id),
            ])
            ->withCount('projects')
            ->withCount('members')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Membuat workspace dan membership owner.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(
        User $user,
        array $data,
    ): Workspace {
        $workspace = DB::transaction(
            function () use ($user, $data): Workspace {
                $workspace = Workspace::query()->create([
                    'owner_id' => $user->id,
                    'name' => $data['name'],
                    'slug' => $this->generateUniqueSlug(
                        $data['name']
                    ),
                    'color' => $data['color'] ?? null,
                    'description' => $data['description'] ?? null,
                ]);

                WorkspaceMember::query()->create([
                    'workspace_id' => $workspace->id,
                    'user_id' => $user->id,
                    'role' => WorkspaceRole::OWNER,
                    'joined_at' => now(),
                ]);

                return $workspace;
            }
        );

        return $this->loadForResponse(
            $workspace,
            $user,
        );
    }

    /**
     * Memuat relasi yang dibutuhkan response.
     */
    public function loadForResponse(
        Workspace $workspace,
        User $user,
    ): Workspace {
        $workspace->load([
            'owner',
            'memberships' => fn($query) => $query
                ->where('user_id', $user->id),
        ]);

        $workspace->loadCount('members');

        return $workspace;
    }

    /**
     * Memperbarui workspace.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(
        Workspace $workspace,
        User $user,
        array $data,
    ): Workspace {
        DB::transaction(
            function () use ($workspace, $data): void {
                if (
                    array_key_exists('name', $data)
                    && $data['name'] !== $workspace->name
                ) {
                    $data['slug'] = $this->generateUniqueSlug(
                        $data['name'],
                        $workspace->id,
                    );
                }

                $workspace->update($data);
            }
        );

        return $this->loadForResponse(
            $workspace->refresh(),
            $user,
        );
    }

    /**
     * Menghapus workspace menggunakan soft delete.
     */
    public function delete(Workspace $workspace): void
    {
        DB::transaction(
            static function () use ($workspace): void {
                $workspace->delete();
            }
        );
    }

    /**
     * Membuat slug yang unik.
     */
    private function generateUniqueSlug(
        string $name,
        ?int $ignoreWorkspaceId = null,
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'workspace';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            $this->slugExists(
                $slug,
                $ignoreWorkspaceId,
            )
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Memeriksa slug termasuk workspace yang sudah di-soft-delete.
     */
    private function slugExists(
        string $slug,
        ?int $ignoreWorkspaceId = null,
    ): bool {
        return Workspace::withTrashed()
            ->where('slug', $slug)
            ->when(
                $ignoreWorkspaceId !== null,
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $ignoreWorkspaceId,
                ),
            )
            ->exists();
    }
}
