<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EvaluationsImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $errors = [];
    protected $imported = 0;
    protected $skipped = 0;
    protected $employeeCache = [];
    protected $criteriaCache = [];

    public function __construct()
    {
        // Cache employees and criterias for better performance
        $this->employeeCache = Employee::pluck('id', 'employee_code')->toArray();
        $this->criteriaCache = Criteria::pluck('id', 'name')->toArray();
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    $this->skipped++;
                    continue;
                }

                // Get employee ID
                $employeeId = $this->getEmployeeId($row['employee_code']);
                if (!$employeeId) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => "Employee with code '{$row['employee_code']}' not found",
                        'data' => $row->toArray()
                    ];
                    $this->skipped++;
                    continue;
                }

                // Get criteria ID
                $criteriaId = $this->getCriteriaId($row['criteria_name']);
                if (!$criteriaId) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => "Criteria '{$row['criteria_name']}' not found",
                        'data' => $row->toArray()
                    ];
                    $this->skipped++;
                    continue;
                }

                // Validate score range
                $score = (int) $row['score'];
                if ($score < 0 || $score > 100) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'error' => "Score must be between 0 and 100. Got: {$score}",
                        'data' => $row->toArray()
                    ];
                    $this->skipped++;
                    continue;
                }

                // Check if evaluation already exists
                $existingEvaluation = Evaluation::where('employee_id', $employeeId)
                    ->where('criteria_id', $criteriaId)
                    ->where('evaluation_period', $row['evaluation_period'])
                    ->first();

                if ($existingEvaluation) {
                    // Update existing evaluation
                    $existingEvaluation->update([
                        'score' => $score,
                    ]);
                } else {
                    // Create new evaluation
                    Evaluation::create([
                        'employee_id' => $employeeId,
                        'criteria_id' => $criteriaId,
                        'score' => $score,
                        'evaluation_period' => $row['evaluation_period'],
                    ]);
                }

                $this->imported++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'error' => $e->getMessage(),
                    'data' => $row->toArray()
                ];
                $this->skipped++;
            }
        }
    }

    public function rules(): array
    {
        return [
            'employee_code' => 'required|string|max:20',
            'criteria_name' => 'required|string|max:255',
            'score' => 'required|integer|min:0|max:100',
            'evaluation_period' => 'required|string|max:50',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_code.required' => 'Employee Code is required',
            'employee_code.max' => 'Employee Code cannot exceed 20 characters',
            'criteria_name.required' => 'Criteria Name is required',
            'criteria_name.max' => 'Criteria Name cannot exceed 255 characters',
            'score.required' => 'Score is required',
            'score.integer' => 'Score must be a number',
            'score.min' => 'Score must be at least 0',
            'score.max' => 'Score cannot exceed 100',
            'evaluation_period.required' => 'Evaluation Period is required',
            'evaluation_period.max' => 'Evaluation Period cannot exceed 50 characters',
        ];
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }

    private function isEmptyRow($row): bool
    {
        return empty($row['employee_code']) && 
               empty($row['criteria_name']) && 
               empty($row['score']) && 
               empty($row['evaluation_period']);
    }

    private function getEmployeeId($employeeCode): ?int
    {
        return $this->employeeCache[$employeeCode] ?? null;
    }

    private function getCriteriaId($criteriaName): ?int
    {
        return $this->criteriaCache[$criteriaName] ?? null;
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