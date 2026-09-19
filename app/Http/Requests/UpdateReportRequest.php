<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reports' => [
                'required',
                'array',
                'min:1',
            ],

            'reports.*.id' => [
                'nullable',
                'integer',
                'distinct',
            ],

            'reports.*.nama_krt' => [
                'required',
                'string',
                'min:2',
                'max:150',
            ],

            'reports.*.urutan' => [
                'required',
                'integer',
                'min:1',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'reports.required' => 'Minimal satu KRT harus diisi.',
            'reports.min' => 'Minimal satu KRT harus diisi.',

            'reports.*.id.integer' => 'ID report tidak valid.',
            'reports.*.id.distinct' => 'ID report tidak boleh duplikat.',

            'reports.*.nama_krt.required' => 'Nama KRT wajib diisi.',
            'reports.*.nama_krt.min' => 'Nama KRT minimal 2 karakter.',
            'reports.*.nama_krt.max' => 'Nama KRT maksimal 150 karakter.',

            'reports.*.urutan.required' => 'Urutan KRT wajib diisi.',
            'reports.*.urutan.min' => 'Urutan KRT tidak valid.',
        ];
    }
}
