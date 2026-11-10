<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\MissionStatus;
use App\Models\Hero;
use App\Models\Quest;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
