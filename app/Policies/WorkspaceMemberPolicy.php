<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Workspace;
use App\Enums\WorkspaceRole;

class WorkspaceMemberPolicy
{
    /**
     * View workspace members.
     */
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $workspace->hasMember($user);
    }

    public function create(User $user, Workspace $workspace): bool
    {
        return $workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    public function delete(User $user, Workspace $workspace): bool
    {
        return $workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    public function restore(User $user, Workspace $workspace): bool
    {
        return $workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
        );
    }

    public function forceDelete(User $user, Workspace $workspace): bool
    {
        return $workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
        );
    }
}
