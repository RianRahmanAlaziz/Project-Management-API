<?php

namespace App\Policies;

use App\Enums\WorkspaceRole;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user, Project $project,): bool
    {
        return $project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
            WorkspaceRole::MEMBER,
            WorkspaceRole::VIEWER,
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
            WorkspaceRole::MEMBER,
            WorkspaceRole::VIEWER,
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project,): bool
    {
        return $project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $task->project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
            WorkspaceRole::MEMBER,
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        return $task->project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $task->project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }
    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $task->project->workspace->hasRole(
            $user,
            WorkspaceRole::OWNER,
            WorkspaceRole::ADMIN,
        );
    }
}
