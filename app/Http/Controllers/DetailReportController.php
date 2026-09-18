<?php

namespace App\Http\Controllers;

use App\Models\DetailReport;
use App\Models\Report;
use Illuminate\Http\Request;

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
        ], [
            'keterangan_error.required' => 'Keterangan temuan wajib diisi.',
            'keterangan_error.min' => 'Keterangan temuan minimal 2 karakter.',
        ]);

        $report->detailReports()->create([
            'keterangan_error' => trim($validated['keterangan_error']),
            'status' => DetailReport::STATUS_DRAFT,
        ]);

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Temuan berhasil ditambahkan.');
    }
}
