<?php

namespace App\Models;

use App\Enums\HeroClass;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
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

    use HasFactory;

     
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'hero_class' => HeroClass::class,
            'status' => HeroStatus::class,
        ];
    }


}
