<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEvaluationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
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
                'between:1,100'
            ],
            'evaluation_period' => [
                'required',
                'string',
                'max:255',
                'regex:/^\d{4}-\d{2}$/', // Format: YYYY-MM
                Rule::unique('evaluations')->where(function ($query) {
                    return $query->where('employee_id', $this->employee_id)
                                 ->where('criteria_id', $this->criteria_id)
                                 ->where('evaluation_period', $this->evaluation_period);
                })
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Karyawan harus dipilih.',
            'employee_id.exists' => 'Karyawan yang dipilih tidak valid.',
            'criteria_id.required' => 'Kriteria harus dipilih.',
            'criteria_id.exists' => 'Kriteria yang dipilih tidak valid.',
            'score.required' => 'Nilai evaluasi harus diisi.',
            'score.between' => 'Nilai evaluasi harus antara 1-100.',
            'evaluation_period.required' => 'Periode evaluasi harus diisi.',
            'evaluation_period.regex' => 'Format periode evaluasi harus YYYY-MM (contoh: 2024-01).',
            'evaluation_period.unique' => 'Evaluasi untuk karyawan ini pada kriteria dan periode yang sama sudah ada.',
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
            'score' => 'nilai evaluasi',
            'evaluation_period' => 'periode evaluasi',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation: Check if evaluation period is not in the future
            if ($this->evaluation_period) {
                $currentPeriod = now()->format('Y-m');
                if ($this->evaluation_period > $currentPeriod) {
                    $validator->errors()->add('evaluation_period', 'Periode evaluasi tidak boleh di masa depan.');
                }
            }

            // Check if criteria weight total is 100%
            $totalWeight = \App\Models\Criteria::sum('weight');
            if ($totalWeight != 100) {
                $validator->errors()->add('criteria_id', "Total bobot kriteria harus 100% (saat ini: {$totalWeight}%).");
            }
        });
    }
}
