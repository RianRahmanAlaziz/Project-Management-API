<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class ProjectMemberService
{
    /**
     * Get paginated project members.
     */
    public function paginateForProject(
        Project $project,
        int $perPage = 15,
        ?string $search = null,
    ): LengthAwarePaginator {
        return $project->memberships()
            ->with('user')
            ->when(
                $search,
                function ($query, $search) {
                    $query->whereHas(
                        'user',
                        function ($query) use ($search) {
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
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Add a member to the project.
     *
     * User must already be a member of the parent workspace.
     */
    public function create(
        Project $project,
        array $data,
    ): ProjectMember {
        $isWorkspaceMember = $project->workspace
            ->hasMemberById($data['user_id']);

        if (! $isWorkspaceMember) {
            throw ValidationException::withMessages([
                'user_id' => [
                    'User bukan anggota workspace ini.',
                ],
            ]);
        }

        $alreadyMember = $project->memberships()
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($alreadyMember) {
            throw ValidationException::withMessages([
                'user_id' => [
                    'User sudah menjadi anggota project ini.',
                ],
            ]);
        }

        $membership = $project->memberships()->create([
            'user_id' => $data['user_id'],
            'role' => $data['role'],
            'joined_at' => now(),
        ]);

        return $membership->load('user');
    }

    /**
     * Update project member role.
     */
    public function update(
        Project $project,
        ProjectMember $membership,
        array $data,
    ): ProjectMember {
        $this->ensureBelongsToProject(
            $project,
            $membership,
        );

        $this->ensureOwnerIsProtected($membership);

        $membership->update([
            'role' => $data['role'],
        ]);

        return $membership->refresh()->load('user');
    }

    /**
     * Remove a member from the project.
     */
    public function delete(
        Project $project,
        ProjectMember $membership,
    ): void {
        $this->ensureBelongsToProject(
            $project,
            $membership,
        );

        $this->ensureOwnerIsProtected($membership);

        $membership->delete();
    }

    /**
     * Ensure membership belongs to requested project.
     */
    private function ensureBelongsToProject(
        Project $project,
        ProjectMember $membership,
    ): void {
        abort_unless(
            $membership->project_id === $project->id,
            404,
        );
    }

    /**
     * Project owner cannot be modified or removed
     * through normal member management.
     */
    private function ensureOwnerIsProtected(
        ProjectMember $membership,
    ): void {
        if ($membership->role === 'owner') {
            throw ValidationException::withMessages([
                'member' => [
                    'Project owner tidak dapat diubah atau dihapus.',
                ],
            ]);
        }
    }
}
