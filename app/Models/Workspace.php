<?php

namespace App\Models;

use App\Enums\WorkspaceRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Workspace extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'color',
        'description',
    ];

    /**
     * Owner utama workspace.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'owner_id',
        );
    }

    /**
     * Record membership workspace.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * Semua user yang menjadi anggota workspace.
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'workspace_members',
        )
            ->withPivot([
                'id',
                'role',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Memeriksa apakah user merupakan anggota workspace.
     */
    public function hasMember(User $user): bool
    {
        return $this->hasMemberById($user->id);
    }

    /**
     * Memeriksa membership berdasarkan user ID.
     */
    public function hasMemberById(int $userId): bool
    {
        return $this->memberships()
            ->where('user_id', $userId)
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

    // public function canManageMembers(User $user): bool
    // {
    //     return $this->hasRole(
    //         $user,
    //         WorkspaceRole::OWNER,
    //         WorkspaceRole::ADMIN,
    //     );
    // }

    /**
     * Route model binding menggunakan slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
