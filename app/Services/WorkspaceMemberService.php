<?php

namespace App\Services;

use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class WorkspaceMemberService
{
    /**
     * Mengambil daftar member workspace.
     */
    public function paginateForWorkspace(
        Workspace $workspace,
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return $workspace
            ->memberships()
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->whereHas(
                        'user',
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%",
                                );
                        },
                    );
                },
            )
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function paginateAvailableMembers(
        Workspace $workspace,
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return User::query()
            ->whereNotIn(
                'id',
                $workspace->memberships()
                    ->select('user_id'),
            )
            ->when(
                $search,
                function ($query, $search): void {
                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%",
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%",
                                );
                        },
                    );
                },
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Menambahkan member ke workspace.
     *
     * @param array<string, mixed> $data
     */

    public function create(
        Workspace $workspace,
        array $data,
    ): WorkspaceMember {
        $member = DB::transaction(
            function () use ($workspace, $data): WorkspaceMember {
                return WorkspaceMember::query()->create([
                    'workspace_id' => $workspace->id,
                    'user_id' => $data['user_id'],
                    'role' => $data['role'],
                    'joined_at' => now(),
                ]);
            }
        );

        return $this->loadForResponse($member);
    }

    /**
     * Memuat relasi yang dibutuhkan response.
     */

    public function loadForResponse(
        WorkspaceMember $member,
    ): WorkspaceMember {
        $member->load('user');

        return $member;
    }

    /**
     * Memperbarui role member workspace.
     *
     * @param array<string, mixed> $data
     */
    public function update(
        WorkspaceMember $member,
        array $data,
    ): WorkspaceMember {
        DB::transaction(
            function () use ($member, $data): void {
                $member->update($data);
            }
        );

        return $this->loadForResponse(
            $member->refresh(),
        );
    }

    /**
     * Menghapus member dari workspace.
     */
    public function delete(
        WorkspaceMember $member,
    ): void {
        DB::transaction(
            static function () use ($member): void {
                $member->delete();
            }
        );
    }
}
