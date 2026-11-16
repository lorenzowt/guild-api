<?php

namespace App\Models;

use App\Enums\MissionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

class Mission extends Model
{
    protected $fillable = [
        'quest_id',
    ];

    protected $attributes = [
        'status' => MissionStatus::Assigned->value,
    ];

    protected function casts(): array
    {
        return [
            'status' => MissionStatus::class,
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function heroes(): BelongsToMany
    {
        return $this->belongsToMany(Hero::class)
            ->withTimestamps();
    }

    public function quest(): BelongsTo
    {
        return $this->BelongsTo(Quest::class);
    }

    public function isAssigned(): bool
    {
        return $this->status === MissionStatus::Assigned;
    }

    public function complete(MissionStatus $outcome): void
    {
        $this->status = $outcome;
        $this->completed_at = now();
    }

    public function cancel(): void
    {
        $this->status = MissionStatus::Canceled;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
