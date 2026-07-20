<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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

            'workspace_id' => $this->workspace_id,
            'owner_id' => $this->owner_id,

            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'status' => $this->status,
            'color' => $this->color,

            'start_date' => $this->start_date?->toISOString(),
            'due_date' => $this->due_date?->toISOString(),

            'members_count' => $this->whenCounted('members'),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
