<?php

namespace App\Http\Requests;

use App\Models\Criteria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCriteriaRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:criterias,name'
            ],
            'weight' => [
                'required',
                'integer',
                'min:1',
                'max:50',
                function ($attribute, $value, $fail) {
                    $currentTotal = Criteria::sum('weight');
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
            // Additional validation logic
            $currentTotal = Criteria::sum('weight');
            $newWeight = $this->weight;
            $newTotal = $currentTotal + $newWeight;

            // Check if there are existing evaluations that would be affected
            if ($newTotal > 100) {
                $validator->errors()->add('weight',
                    "Tidak dapat menambah kriteria karena total bobot akan menjadi {$newTotal}% (melebihi 100%)."
                );
            }

            // Check for duplicate similar names
            $similarNames = Criteria::where('name', 'LIKE', '%' . trim($this->name) . '%')->exists();
            if ($similarNames) {
                $validator->errors()->add('name',
                    'Sudah ada kriteria dengan nama serupa. Pastikan nama kriteria unik.'
                );
            }
        });
    }
}




