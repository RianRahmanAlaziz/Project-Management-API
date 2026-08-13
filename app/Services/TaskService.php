<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;

class TaskService
{
    public function paginate(
        Project $project,
        ?string $search = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return Task::query()
            ->where('project_id', $project->id)
            ->with([
                'creator',
                'assignee',
                'kanbanColumn',
            ])
            ->when(
                $search,
                function ($query, $search) {
                    $query->where(
                        'title',
                        'like',
                        "%{$search}%"
                    );
                }
            )
            ->orderBy('position')
            ->paginate($perPage);
    }

    public function paginateForUser(
        User $user,
        ?string $search = null,
        int $perPage = 15,
    ): LengthAwarePaginator {
        return Task::query()
            ->where('assignee_id', $user->id)
            ->whereHas('project.workspace')
            ->with([
                'project.workspace',
                'kanbanColumn',
                'creator',
                'assignee',
            ])
            ->when(
                filled($search),
                function ($query) use ($search): void {
                    $query->where(
                        'title',
                        'like',
                        "%{$search}%",
                    );
                },
            )
            ->orderByRaw(
                'CASE WHEN due_date IS NULL THEN 1 ELSE 0 END',
            )
            ->orderBy('due_date')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function create(
        Project $project,
        User $creator,
        array $data,
    ): Task {

        $this->validateAssignee(
            $project,
            $data['assignee_id'] ?? null,
        );

        return Task::create([
            'project_id' => $project->id,

            'column_id' => $data['column_id'],

            'creator_id' => $creator->id,

            'assignee_id' => $data['assignee_id'] ?? null,

            'title' => $data['title'],

            'description' => $data['description'] ?? null,

            'priority' => $data['priority'] ?? 'medium',

            'position' => $this->getNextPosition(
                $data['column_id'],
            ),

            'start_date' => $data['start_date'] ?? null,

            'due_date' => $data['due_date'] ?? null,
        ]);
    }

    public function update(
        Task $task,
        array $data,
    ): Task {

        if (array_key_exists('assignee_id', $data)) {

            $this->validateAssignee(
                $task->project,
                $data['assignee_id'],
            );
        }

        $task->update(
            Arr::only(
                $data,
                [
                    'column_id',
                    'assignee_id',
                    'title',
                    'description',
                    'priority',
                    'start_date',
                    'due_date',
                ],
            )
        );

        return $task->refresh();
    }

    public function delete(
        Task $task,
    ): void {
        $task->delete();
    }

    protected function getNextPosition(
        int $columnId,
    ): int {
        return (
            Task::query()
            ->where('column_id', $columnId)
            ->max('position')
            ?? 0
        ) + 1;
    }

    protected function validateAssignee(
        Project $project,
        ?int $assigneeId,
    ): void {
        if ($assigneeId === null) {
            return;
        }

        if (! $project->workspace->hasMemberById($assigneeId)) {
            throw ValidationException::withMessages([
                'assignee_id' => [
                    'User bukan anggota workspace.',
                ],
            ]);
        }
    }
}
