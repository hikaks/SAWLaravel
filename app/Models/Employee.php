<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_code',
        'name',
        'position',
        'department',
        'email',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all evaluations for this employee.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Get all evaluation results for this employee.
     */
    public function evaluationResults(): HasMany
    {
        return $this->hasMany(EvaluationResult::class);
    }

    /**
     * Get evaluations for a specific period.
     */
    public function evaluationsForPeriod(string $period): HasMany
    {
        return $this->evaluations()->where('evaluation_period', $period);
    }

    /**
     * Get latest evaluation result.
     */
    public function latestResult()
    {
        return $this->evaluationResults()->latest('evaluation_period')->first();
    }
}
