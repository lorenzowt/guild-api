<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Quest extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'boss',
        'difficulty',
        'is_active'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): void 
    {
        $query->where('is_active', true);
    }
}
