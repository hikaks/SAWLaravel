<?php

namespace App\Rules;

use App\Models\Criteria;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCriteriaWeight implements ValidationRule
{
    protected $excludeId;
    protected $maxTotalWeight;

    /**
     * Create a new rule instance.
     *
     * @param int|null $excludeId ID kriteria yang dikecualikan (untuk update)
     * @param int $maxTotalWeight Total maksimal bobot yang diizinkan
     */
    public function __construct($excludeId = null, $maxTotalWeight = 100)
    {
        $this->excludeId = $excludeId;
        $this->maxTotalWeight = $maxTotalWeight;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Validasi dasar
        if (!is_numeric($value) || $value < 1) {
            $fail('Bobot kriteria minimal 1%.');
            return;
        }

        if ($value > 50) {
            $fail('Bobot kriteria maksimal 50% per kriteria.');
            return;
        }

        // Hitung total bobot saat ini (excluding current criteria if updating)
        $query = Criteria::query();
        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        $currentTotal = $query->sum('weight');
        $newTotal = $currentTotal + $value;

        // Cek apakah total melebihi batas maksimal
        if ($newTotal > $this->maxTotalWeight) {
            $remaining = $this->maxTotalWeight - $currentTotal;
            $excess = $newTotal - $this->maxTotalWeight;

            $fail("Total bobot akan menjadi {$newTotal}%, melebihi maksimal {$this->maxTotalWeight}%. " .
                  "Sisa bobot yang tersedia: {$remaining}%. " .
                  "Kurangi {$excess}% dari nilai yang dimasukkan.");
            return;
        }

        // Peringatan jika mendekati batas maksimal (90% dari total)
        $warningThreshold = $this->maxTotalWeight * 0.9;
        if ($newTotal >= $warningThreshold) {
            // This would be handled in the form request's withValidator method
            // as warnings are not directly supported in ValidationRule
        }
    }

    /**
     * Get remaining weight available.
     *
     * @return int
     */
    public function getRemainingWeight(): int
    {
        $query = Criteria::query();
        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        $currentTotal = $query->sum('weight');
        return $this->maxTotalWeight - $currentTotal;
    }

    /**
     * Check if adding this weight would complete the total.
     *
     * @param int $weight
     * @return bool
     */
    public function wouldCompleteTotal($weight): bool
    {
        $query = Criteria::query();
        if ($this->excludeId) {
            $query->where('id', '!=', $this->excludeId);
        }

        $currentTotal = $query->sum('weight');
        return ($currentTotal + $weight) == $this->maxTotalWeight;
    }

    /**
     * Get optimal weight suggestions.
     *
     * @return array
     */
    public function getWeightSuggestions(): array
    {
        $remaining = $this->getRemainingWeight();

        $suggestions = [];

        // Common weight values
        $commonWeights = [5, 10, 15, 20, 25, 30];

        foreach ($commonWeights as $weight) {
            if ($weight <= $remaining) {
                $suggestions[] = $weight;
            }
        }

        // Add remaining weight as option if not already present
        if ($remaining > 0 && !in_array($remaining, $suggestions)) {
            $suggestions[] = $remaining;
        }

        return array_unique($suggestions);
    }
}
