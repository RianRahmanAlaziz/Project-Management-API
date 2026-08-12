<?php

namespace App\Http\Resources\V1;


use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Project
 */
class ProjectDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'workspace_id' => $this->workspace_id,
            'workspace' => $this->whenLoaded(
                'workspace',
                fn() => [
                    'id' => $this->workspace->id,
                    'name' => $this->workspace->name,
                    'slug' => $this->workspace->slug,
                    'description' => $this->workspace->description,
                    'color' => $this->workspace->color,
                ],
            ),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'status' => $this->status,
            'priority' => $this->priority,
            'progress' => $this->progress,
            'color' => $this->color,

            'member_count' => $this->members_count
                ?? $this->members()->count(),

            'tasks_count' => $this->tasks_count
                ?? 0,

            'start_date' => $this->start_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
