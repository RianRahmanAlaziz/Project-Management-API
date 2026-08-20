<?php

namespace App\Http\Resources\V1\Tasks;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TasksResource extends JsonResource
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
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'column' => $this->whenLoaded(
                'kanbanColumn',
                fn() => [
                    'id' => $this->kanbanColumn->id,
                    'name' => $this->kanbanColumn->name,
                    'description' => $this->kanbanColumn->description,
                    'color' => $this->kanbanColumn->color,
                    'position' => $this->kanbanColumn->position,
                    'is_completed' => $this->kanbanColumn->is_completed,
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
        ];
    }
}
