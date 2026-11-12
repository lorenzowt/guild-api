<?php

namespace App\Models;

use App\Enums\HeroClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use App\Models\Mission;
use App\Enums\HeroStatus;
use Illuminate\Database\Eloquent\Model;
use Database\Factories\HeroFactory;

class Hero extends Model
{
    use HasFactory;
    
    private const MIN_LEVEL = 1;
    private const MAX_LEVEL = 10;

    protected $fillable = [
        'name',
        'hero_class',
    ];

    protected $attributes = [
        'level' => 1,
        'status' => HeroStatus::Available->value,
    ];

     
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function missions(): BelongsToMany
    {
        return $this->belongsToMany(Mission::class);
    }

    public function scopeActive(Builder $query) 
    {
        $query->where('status', '!=', HeroStatus::Deactivated);
    }

    public function isInjured(): bool
    {
        return $this->status === HeroStatus::Injured;
    }

     public function levelUp(): void
    {
        $this->level = min($this->level + 1, self::MAX_LEVEL);
    }

    public function levelDown(): void
    {
        $this->level = max($this->level - 1, self::MIN_LEVEL);
    }

    public function makeAvailable(): void
    {
        $this->status = HeroStatus::Available;
    }

    public function injure(): void
    {
        $this->status = HeroStatus::Injured;
    }

    protected function casts(): array
    {
        return [
            'hero_class' => HeroClass::class,
            'status' => HeroStatus::class,
        ];
    }
    
}
