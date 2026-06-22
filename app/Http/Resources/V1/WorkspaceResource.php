<?php

namespace App\Http\Resources\V1;

use App\Enums\WorkspaceRole;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $membership = $this->relationLoaded('memberships')
            ? $this->memberships->first()
            : null;

        $currentRole = $membership?->role;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'owner' => UserResource::make(
                $this->whenLoaded('owner')
            ),

            'current_user_role' => $currentRole instanceof WorkspaceRole
                ? $currentRole->value
                : $currentRole,

            'members_count' => $this->whenCounted('members'),

            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
