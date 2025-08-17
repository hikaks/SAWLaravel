<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Criteria extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'weight',
        'type',
    ];

    protected $casts = [
        'weight' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all evaluations for this criteria.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Check if criteria is benefit type.
     */
    public function isBenefit(): bool
    {
        return $this->type === 'benefit';
    }

    /**
     * Check if criteria is cost type.
     */
    public function isCost(): bool
    {
        return $this->type === 'cost';
    }

    /**
     * Get weight as percentage.
     */
    public function getWeightPercentageAttribute(): float
    {
        return $this->weight / 100;
    }

    /**
     * Scope to get benefit criteria.
     */
    public function scopeBenefit($query)
    {
        return $query->where('type', 'benefit');
    }

    /**
     * Scope to get cost criteria.
     */
    public function scopeCost($query)
    {
        return $query->where('type', 'cost');
    }
}
