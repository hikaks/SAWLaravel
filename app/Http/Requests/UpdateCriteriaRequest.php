<?php

namespace App\Http\Requests;

use App\Models\Criteria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCriteriaRequest extends FormRequest
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
        $criteriaId = $this->route('criteria') ? $this->route('criteria')->id : $this->route('criteria');

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('criterias', 'name')->ignore($criteriaId)
            ],
            'weight' => [
                'required',
                'integer',
                'min:1',
                'max:50',
                function ($attribute, $value, $fail) use ($criteriaId) {
                    $currentTotal = Criteria::where('id', '!=', $criteriaId)->sum('weight');
                    $newTotal = $currentTotal + $value;

                    if ($newTotal > 100) {
                        $remaining = 100 - $currentTotal;
                        $fail("Total bobot akan menjadi {$newTotal}%, melebihi maksimal 100%. Sisa bobot yang tersedia: {$remaining}%");
                    }
                }
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['benefit', 'cost'])
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kriteria wajib diisi.',
            'name.min' => 'Nama kriteria minimal 3 karakter.',
            'name.max' => 'Nama kriteria maksimal 255 karakter.',
            'name.unique' => 'Nama kriteria sudah digunakan, silakan gunakan nama lain.',

            'weight.required' => 'Bobot kriteria wajib diisi.',
            'weight.integer' => 'Bobot kriteria harus berupa angka.',
            'weight.min' => 'Bobot kriteria minimal 1%.',
            'weight.max' => 'Bobot kriteria maksimal 50% per kriteria.',

            'type.required' => 'Tipe kriteria wajib dipilih.',
            'type.in' => 'Tipe kriteria harus benefit atau cost.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama kriteria',
            'weight' => 'bobot kriteria',
            'type' => 'tipe kriteria'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => ucwords(strtolower($this->name)),
            'weight' => (int) $this->weight,
            'type' => strtolower($this->type)
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Additional processing after validation passes
        $this->merge([
            'name' => trim($this->name)
        ]);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $criteriaId = $this->route('criteria') ? $this->route('criteria')->id : $this->route('criteria');
            $criteria = Criteria::find($criteriaId);

            if (!$criteria) {
                $validator->errors()->add('criteria', 'Kriteria tidak ditemukan.');
                return;
            }

            // Check if changing weight affects existing evaluations
            if ($criteria->weight != $this->weight) {
                $evaluationCount = $criteria->evaluations()->count();
                if ($evaluationCount > 0) {
                    $validator->warnings ??= [];
                    $validator->warnings['weight'] = "Perubahan bobot akan mempengaruhi {$evaluationCount} evaluasi yang sudah ada. Hasil SAW perlu dihitung ulang.";
                }
            }

            // Check if changing type affects existing evaluations
            if ($criteria->type != $this->type) {
                $evaluationCount = $criteria->evaluations()->count();
                if ($evaluationCount > 0) {
                    $validator->warnings ??= [];
                    $validator->warnings['type'] = "Perubahan tipe kriteria akan mempengaruhi {$evaluationCount} evaluasi yang sudah ada. Hasil SAW perlu dihitung ulang.";
                }
            }
        });
    }
}


