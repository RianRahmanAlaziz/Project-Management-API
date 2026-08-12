<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KanbanColumn extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'project_id',
        'name',
        'color',
        'enabled',
        'position',
        'is_completed',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'position' => 'integer',
            'is_completed' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'column_id',
        )->orderBy('position');
    }
}
