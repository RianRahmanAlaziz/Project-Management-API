<?php

namespace App\Http\Resources\V1;

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
            'column_id' => $this->column_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'position' => $this->position,
            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'creator' => $this->whenLoaded(
                'creator',
                fn() => [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ],
            ),
            'assignee' => $this->whenLoaded(
                'assignee',
                fn() => $this->assignee
                    ? [
                        'id' => $this->assignee->id,
                        'name' => $this->assignee->name,
                    ]
                    : null,
            ),
        ];
    }
}
