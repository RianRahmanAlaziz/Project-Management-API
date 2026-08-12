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
        return $project->kanbanColumns()
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
                    $project->kanbanColumns()
                    ->max('position') ?? 0
                ) + 1;

                return $project->kanbanColumns()->create([
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
        $column->update($data);

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
        DB::transaction(function () use (
            $project,
            $columns,
        ): void {
            /*
         * Lock project columns during reorder
         * to prevent concurrent updates.
         */
            $projectColumns = $project->kanbanColumns()
                ->lockForUpdate()
                ->get();

            $maxPosition = $projectColumns->max(
                'position'
            ) ?? 0;

            /*
         * Step 1:
         * Move affected columns to temporary
         * positive positions outside the current range.
         */
            foreach ($columns as $index => $item) {
                $project->kanbanColumns()
                    ->whereKey($item['id'])
                    ->update([
                        'position' =>
                        $maxPosition + $index + 1,
                    ]);
            }

            /*
         * Step 2:
         * Apply final positions.
         */
            foreach ($columns as $item) {
                $project->kanbanColumns()
                    ->whereKey($item['id'])
                    ->update([
                        'position' => $item['position'],
                    ]);
            }
        });
    }
}
