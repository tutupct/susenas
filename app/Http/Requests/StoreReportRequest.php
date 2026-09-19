<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'petugas_id' => [
                'required',
                'integer',
                'exists:petugas,id',
            ],

            'reports' => [
                'required',
                'array',
                'min:1',
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
            'petugas_id.required' => 'Petugas wajib dipilih.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak valid.',

            'reports.required' => 'Minimal satu KRT harus diisi.',
            'reports.min' => 'Minimal satu KRT harus diisi.',

            'reports.*.nama_krt.required' => 'Nama KRT wajib diisi.',
            'reports.*.nama_krt.min' => 'Nama KRT minimal 2 karakter.',
            'reports.*.nama_krt.max' => 'Nama KRT maksimal 150 karakter.',

            'reports.*.urutan.required' => 'Urutan KRT wajib diisi.',
            'reports.*.urutan.min' => 'Urutan KRT tidak valid.',
        ];
    }
}
