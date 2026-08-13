<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyTasksResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'position' => $this->position,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'workspace' => $this->whenLoaded(
                'project.workspace',
                fn() => [
                    'id' => $this->project->workspace->id,
                    'name' => $this->project->workspace->name,
                    'slug' => $this->project->workspace->slug,
                ],
            ),
            'project' => $this->whenLoaded(
                'project',
                fn() => [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'slug' => $this->project->slug,
                    'color' => $this->project->color,
                ],
            ),
            'column' => $this->whenLoaded(
                'kanbanColumn',
                fn() => $this->kanbanColumn
                    ? [
                        'id' => $this->kanbanColumn->id,
                        'name' => $this->kanbanColumn->name,
                        'color' => $this->kanbanColumn->color,
                        'position' => $this->kanbanColumn->position,
                        'is_completed' =>
                        $this->kanbanColumn->is_completed,
                    ]
                    : null,
            ),
            'creator' => $this->whenLoaded(
                'creator',
                fn() => [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ],
            ),
        ];
    }
}
