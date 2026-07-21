<?php

namespace App\Services;

use App\Models\KanbanColumn;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class KanbanColumnService
{
    /**
     * Get all kanban columns for a project.
     */
    public function getForProject(
        Project $project,
    ): Collection {
        return $project->columns()
            ->orderBy('position')
            ->get();
    }

    /**
     * Create a new kanban column.
     */
    public function create(
        Project $project,
        array $data,
    ): KanbanColumn {
        return DB::transaction(
            function () use (
                $project,
                $data,
            ): KanbanColumn {
                $nextPosition = (
                    $project->columns()
                    ->max('position') ?? -1
                ) + 1;

                return $project->columns()->create([
                    'name' => $data['name'],
                    'color' => $data['color'] ?? null,
                    'position' => $nextPosition,
                ]);
            },
        );
    }

    /**
     * Update kanban column.
     */
    public function update(
        KanbanColumn $column,
        array $data,
    ): KanbanColumn {
        $column->update([
            'name' => $data['name'],
            'color' => $data['color'] ?? null,
        ]);

        return $column->refresh();
    }

    /**
     * Delete kanban column.
     */
    public function delete(
        KanbanColumn $column,
    ): void {
        DB::transaction(function () use ($column): void {
            $projectId = $column->project_id;
            $deletedPosition = $column->position;

            $column->delete();

            KanbanColumn::query()
                ->where('project_id', $projectId)
                ->where('position', '>', $deletedPosition)
                ->decrement('position');
        });
    }

    /**
     * Reorder kanban columns.
     *
     * @param array<int, array{id: int, position: int}> $columns
     */
    public function reorder(
        Project $project,
        array $columns,
    ): void {
        DB::transaction(
            function () use (
                $project,
                $columns,
            ): void {
                foreach ($columns as $item) {
                    $project->columns()
                        ->whereKey($item['id'])
                        ->update([
                            'position' => $item['position'],
                        ]);
                }
            },
        );
    }
}
