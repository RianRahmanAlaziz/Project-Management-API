<?php

namespace App\Models;

use App\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'workspace_id',
        'owner_id',
        'name',
        'slug',
        'description',
        'status',
        'color',
        'start_date',
        'due_date',
    ];

    /**
     * @return array<string, string>
     */

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'due_date' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_id',
        );
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'project_members',
        )
            ->withPivot([
                'id',
                'role',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function hasMember(User $user): bool
    {
        return $this->memberships()
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Memeriksa apakah user memiliki salah satu role.
     */
    public function hasRole(
        User $user,
        WorkspaceRole ...$roles,
    ): bool {
        $roleValues = array_map(
            static fn(WorkspaceRole $role): string => $role->value,
            $roles,
        );

        return $this->memberships()
            ->where('user_id', $user->id)
            ->whereIn('role', $roleValues)
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
