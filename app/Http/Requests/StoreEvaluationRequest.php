<?php

namespace App\Http\Requests;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',
                'exists:employees,id'
            ],
            'criteria_id' => [
                'required',
                'integer',
                'exists:criterias,id'
            ],
            'score' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'evaluation_period' => [
                'required',
                'string',
                'regex:/^\d{4}-\d{2}$/',
                function ($attribute, $value, $fail) {
                    // Check if period is not in the future
                    $year = (int) substr($value, 0, 4);
                    $month = (int) substr($value, 5, 2);
                    $currentYear = date('Y');
                    $currentMonth = date('n');

                    if ($year > $currentYear || ($year == $currentYear && $month > $currentMonth)) {
                        $fail('Periode evaluasi tidak boleh lebih dari bulan saat ini.');
                    }

                    if ($month < 1 || $month > 12) {
                        $fail('Bulan pada periode evaluasi tidak valid (01-12).');
                    }
                }
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan wajib dipilih.',
            'employee_id.integer' => 'ID karyawan tidak valid.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak ditemukan.',

            'criteria_id.required' => 'Kriteria wajib dipilih.',
            'criteria_id.integer' => 'ID kriteria tidak valid.',
            'criteria_id.exists' => 'Kriteria yang dipilih tidak ditemukan.',

            'score.required' => 'Skor penilaian wajib diisi.',
            'score.integer' => 'Skor penilaian harus berupa angka.',
            'score.min' => 'Skor penilaian minimal 1.',
            'score.max' => 'Skor penilaian maksimal 100.',

            'evaluation_period.required' => 'Periode evaluasi wajib diisi.',
            'evaluation_period.regex' => 'Format periode evaluasi harus YYYY-MM (contoh: 2024-01).'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_id' => 'karyawan',
            'criteria_id' => 'kriteria',
            'score' => 'skor penilaian',
            'evaluation_period' => 'periode evaluasi'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_id' => (int) $this->employee_id,
            'criteria_id' => (int) $this->criteria_id,
            'score' => (int) $this->score,
            'evaluation_period' => trim($this->evaluation_period)
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check for duplicate evaluation
            $existingEvaluation = Evaluation::where([
                'employee_id' => $this->employee_id,
                'criteria_id' => $this->criteria_id,
                'evaluation_period' => $this->evaluation_period
            ])->first();

            if ($existingEvaluation) {
                $employee = Employee::find($this->employee_id);
                $criteria = Criteria::find($this->criteria_id);

                $validator->warnings ??= [];
                $validator->warnings['duplicate'] =
                    "Evaluasi untuk {$employee->name} pada kriteria {$criteria->name} periode {$this->evaluation_period} sudah ada dengan skor {$existingEvaluation->score}. Data akan ditimpa jika dilanjutkan.";
            }

            // Validate score based on criteria type
            if ($this->criteria_id && $this->score) {
                $criteria = Criteria::find($this->criteria_id);

                if ($criteria && $criteria->type === 'cost' && $this->score > 80) {
                    $validator->warnings ??= [];
                    $validator->warnings['score'] =
                        "Kriteria '{$criteria->name}' bertipe Cost (semakin rendah semakin baik). Skor tinggi ({$this->score}) mungkin tidak sesuai.";
                }

                if ($criteria && $criteria->type === 'benefit' && $this->score < 50) {
                    $validator->warnings ??= [];
                    $validator->warnings['score'] =
                        "Kriteria '{$criteria->name}' bertipe Benefit (semakin tinggi semakin baik). Skor rendah ({$this->score}) mungkin perlu dipertimbangkan.";
                }
            }

            // Check evaluation completeness for the period
            if ($this->employee_id && $this->evaluation_period) {
                $employee = Employee::find($this->employee_id);
                $totalCriteria = Criteria::count();
                $existingEvaluations = Evaluation::where([
                    'employee_id' => $this->employee_id,
                    'evaluation_period' => $this->evaluation_period
                ])->count();

                if ($existingEvaluations + 1 == $totalCriteria) {
                    $validator->info ??= [];
                    $validator->info['completion'] =
                        "Ini adalah evaluasi terakhir untuk {$employee->name} periode {$this->evaluation_period}. Setelah ini, Anda dapat menjalankan perhitungan SAW.";
                }
            }
        });
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Log evaluation activity (optional)
        // Note: Activity logging can be implemented later with spatie/laravel-activitylog
        // For now, we can use Laravel's built-in logging
        \Log::info('Evaluation created', [
            'employee_id' => $this->employee_id,
            'criteria_id' => $this->criteria_id,
            'score' => $this->score,
            'period' => $this->evaluation_period,
            'created_by' => auth()->id() ?? 'system'
        ]);
    }
}
