<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class EmployeesImport implements ToCollection, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    use Importable;

    protected $errors = [];
    protected $imported = 0;
    protected $skipped = 0;

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                // Skip empty rows
                if ($this->isEmptyRow($row)) {
                    $this->skipped++;
                    continue;
                }

                // Check if employee already exists
                $existingEmployee = Employee::where('employee_code', $row['employee_code'])
                    ->orWhere('email', $row['email'])
                    ->first();

                if ($existingEmployee) {
                    // Update existing employee
                    $existingEmployee->update([
                        'name' => $row['name'],
                        'position' => $row['position'],
                        'department' => $row['department'],
                        'email' => $row['email'],
                    ]);
                } else {
                    // Create new employee
                    Employee::create([
                        'employee_code' => $row['employee_code'],
                        'name' => $row['name'],
                        'position' => $row['position'],
                        'department' => $row['department'],
                        'email' => $row['email'],
                    ]);
                }

                $this->imported++;

            } catch (\Exception $e) {
                $this->errors[] = [
                    'row' => $index + 2, // +2 because of header and 0-based index
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
            'employee_code' => [
                'required',
                'string',
                'max:20',
            ],
            'name' => 'required|string|max:255',
            'position' => 'required|string|max:100',
            'department' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
            ],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'employee_code.required' => 'Employee Code is required',
            'employee_code.max' => 'Employee Code cannot exceed 20 characters',
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'position.required' => 'Position is required',
            'position.max' => 'Position cannot exceed 100 characters',
            'department.required' => 'Department is required',
            'department.max' => 'Department cannot exceed 100 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    private function isEmptyRow($row): bool
    {
        return empty($row['employee_code']) && 
               empty($row['name']) && 
               empty($row['position']) && 
               empty($row['department']) && 
               empty($row['email']);
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