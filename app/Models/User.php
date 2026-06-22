<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Workspace yang dimiliki langsung oleh user.
     */
    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(
            Workspace::class,
            'owner_id',
        );
    }

    /**
     * Semua record membership user.
     */
    public function workspaceMemberships(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class);
    }

    /**
     * Semua workspace yang diikuti user.
     */
    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(
            Workspace::class,
            'workspace_members',
        )
            ->withPivot([
                'id',
                'role',
                'joined_at',
            ])
            ->withTimestamps();
    }
}
