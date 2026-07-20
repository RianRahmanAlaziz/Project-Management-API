<?php

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\User;

class ProjectMemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->hasMember($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $Project): bool
    {
        return $Project->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $Project): bool
    {
        return $Project->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $Project): bool
    {
        return $Project->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $Project): bool
    {
        return $Project->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $Project): bool
    {
        return $Project->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }
}
