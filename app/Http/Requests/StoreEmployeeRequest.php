<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
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
            'employee_code' => [
                'required',
                'string',
                'min:3',
                'max:20',
                'regex:/^[A-Z0-9]+$/',
                'unique:employees,employee_code'
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-Z\s\.]+$/'
            ],
            'position' => [
                'required',
                'string',
                'min:2',
                'max:100'
            ],
            'department' => [
                'required',
                'string',
                'min:2',
                'max:100'
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:employees,email'
            ]
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'employee_code.required' => 'Kode karyawan wajib diisi.',
            'employee_code.min' => 'Kode karyawan minimal 3 karakter.',
            'employee_code.max' => 'Kode karyawan maksimal 20 karakter.',
            'employee_code.regex' => 'Kode karyawan hanya boleh mengandung huruf besar dan angka.',
            'employee_code.unique' => 'Kode karyawan sudah digunakan, silakan gunakan kode lain.',

            'name.required' => 'Nama lengkap wajib diisi.',
            'name.min' => 'Nama minimal 2 karakter.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'name.regex' => 'Nama hanya boleh mengandung huruf, spasi, dan titik.',

            'position.required' => 'Posisi wajib diisi.',
            'position.min' => 'Posisi minimal 2 karakter.',
            'position.max' => 'Posisi maksimal 100 karakter.',

            'department.required' => 'Department wajib diisi.',
            'department.min' => 'Department minimal 2 karakter.',
            'department.max' => 'Department maksimal 100 karakter.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah digunakan, silakan gunakan email lain.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'employee_code' => 'kode karyawan',
            'name' => 'nama lengkap',
            'position' => 'posisi',
            'department' => 'department',
            'email' => 'email'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'employee_code' => strtoupper($this->employee_code),
            'name' => ucwords(strtolower($this->name)),
            'email' => strtolower($this->email)
        ]);
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Additional processing after validation passes
        $this->merge([
            'name' => trim($this->name),
            'position' => trim($this->position),
            'department' => trim($this->department),
            'email' => trim($this->email)
        ]);
    }
}




