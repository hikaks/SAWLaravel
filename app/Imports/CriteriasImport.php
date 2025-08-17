<?php

namespace App\Imports;

use App\Models\Criteria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class CriteriasImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $errors = [];
    protected $imported = 0;
    protected $skipped = 0;
    protected $totalWeight = 0;

    public function collection(Collection $rows)
    {
        // First pass: Calculate total weight and validate
        $validRows = [];
        $totalWeight = 0;

        foreach ($rows as $index => $row) {
            if ($this->isEmptyRow($row)) {
                $this->skipped++;
                continue;
            }

            $weight = (int) $row['weight'];
            $totalWeight += $weight;
            $validRows[] = ['index' => $index, 'row' => $row, 'weight' => $weight];
        }

        // Validate total weight
        if ($totalWeight != 100) {
            $this->errors[] = [
                'row' => 'All',
                'error' => "Total weight must equal 100%. Current total: {$totalWeight}%",
                'data' => ['total_weight' => $totalWeight]
            ];
            return;
        }

        // Second pass: Import data
        foreach ($validRows as $item) {
            try {
                $row = $item['row'];
                $index = $item['index'];

                // Check if criteria already exists
                $existingCriteria = Criteria::where('name', $row['name'])->first();

                if ($existingCriteria) {
                    // Update existing criteria
                    $existingCriteria->update([
                        'name' => $row['name'],
                        'weight' => (int) $row['weight'],
                        'type' => strtolower($row['type']),
                    ]);
                } else {
                    // Create new criteria
                    Criteria::create([
                        'name' => $row['name'],
                        'weight' => (int) $row['weight'],
                        'type' => strtolower($row['type']),
                    ]);
                }

                $this->imported++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $item['index'] + 2,
                    'error' => $e->getMessage(),
                    'data' => $item['row']->toArray()
                ];
                $this->skipped++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:1|max:100',
            'type' => 'required|string|in:benefit,cost',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Criteria name is required',
            'name.max' => 'Criteria name cannot exceed 255 characters',
            'weight.required' => 'Weight is required',
            'weight.integer' => 'Weight must be a number',
            'weight.min' => 'Weight must be at least 1',
            'weight.max' => 'Weight cannot exceed 100',
            'type.required' => 'Type is required',
            'type.in' => 'Type must be either "benefit" or "cost"',
        ];
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function chunkSize(): int
    {
        return 50;
    }

    private function isEmptyRow($row): bool
    {
        return empty($row['name']) && 
               empty($row['weight']) && 
               empty($row['type']);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImported(): int
    {
        return $this->imported;
    }

    public function getSkipped(): int
    {
        return $this->skipped;
    }

    public function getStats(): array
    {
        return [
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'errors' => count($this->errors),
            'total_processed' => $this->imported + $this->skipped,
        ];
    }
}