<?php

namespace App\Http\Resources\V1\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TasksDetailResource extends JsonResource
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
            'project' => $this->whenLoaded(
                'project',
                fn() => [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'slug' => $this->project->slug,
                ],
            ),
            'column' => $this->whenLoaded(
                'kanbanColumn',
                fn() => [
                    'id' => $this->kanbanColumn->id,
                    'name' => $this->kanbanColumn->name,
                    'position' => $this->kanbanColumn->position,
                ],
            ),
            'creator' => $this->whenLoaded(
                'creator',
                fn() => [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ],
            ),
            'assignee' => $this->whenLoaded(
                'assignee',
                fn() => $this->assignee
                    ? [
                        'id' => $this->assignee->id,
                        'name' => $this->assignee->name,
                        'email' => $this->assignee->email,
                    ]
                    : null,
            ),
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'position' => $this->position,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
