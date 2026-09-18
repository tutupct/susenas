<?php

namespace App\Http\Controllers;

use App\Models\DetailReport;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DetailReportController extends Controller
{
    public function store(Request $request, Report $report)
    {
        $validated = $request->validate([
            'keterangan_error' => [
                'required',
                'string',
                'min:2',
            ],

            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ], [
            'keterangan_error.required' => 'Keterangan temuan wajib diisi.',
            'keterangan_error.min' => 'Keterangan temuan minimal 2 karakter.',

            'foto.image' => 'File yang dipilih harus berupa gambar.',
            'foto.mimes' => 'Foto harus berformat JPG, JPEG, PNG, atau WEBP.',
            'foto.max' => 'Ukuran foto maksimal 5 MB.',
        ]);

        $fotoPath = null;

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')
                ->store("detail-reports/{$report->id}", 'public');
        }

        $report->detailReports()->create([
            'keterangan_error' => trim($validated['keterangan_error']),
            'foto' => $fotoPath,
            'status' => DetailReport::STATUS_DRAFT,
        ]);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Temuan berhasil ditambahkan.');
    }
}
