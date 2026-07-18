<?php

namespace App\Services;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * Get paginated projects belonging to a workspace.
     */
    public function paginateForProject(
        Workspace $workspace,
        ?string $search = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return $workspace->projects()
            ->with('owner')
            ->when(
                $search,
                fn($query, $search) => $query->where(
                    'name',
                    'like',
                    "%{$search}%",
                ),
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new project.
     */
    public function create(
        Workspace $workspace,
        User $user,
        array $data,
    ): Project {
        return DB::transaction(function () use (
            $workspace,
            $user,
            $data,
        ): Project {
            $project = $workspace->projects()->create([
                ...$data,

                'owner_id' => $user->id,

                'slug' => $this->generateUniqueSlug(
                    $workspace,
                    $data['name'],
                ),
            ]);

            $project->memberships()->create([
                'user_id' => $user->id,
                'role' => WorkspaceRole::OWNER->value,
                'joined_at' => now(),
            ]);

            return $project->load([
                'owner',
                'members',
            ]);
        });
    }

    /**
     * Update project.
     */
    public function update(
        Project $project,
        array $data,
    ): Project {
        if (
            isset($data['name'])
            && $data['name'] !== $project->name
        ) {
            $data['slug'] = $this->generateUniqueSlug(
                $project->workspace,
                $data['name'],
                $project->id,
            );
        }

        $project->update($data);

        return $project->refresh()->load([
            'owner',
            'members',
        ]);
    }

    /**
     * Soft delete project.
     */
    public function delete(Project $project): void
    {
        $project->delete();
    }

    /**
     * Generate unique project slug inside a workspace.
     */
    private function generateUniqueSlug(
        Workspace $workspace,
        string $name,
        ?int $ignoreProjectId = null,
    ): string {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Project::query()
            ->where('workspace_id', $workspace->id)
            ->where('slug', $slug)
            ->when(
                $ignoreProjectId,
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $ignoreProjectId,
                ),
            )
            ->withTrashed()
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
