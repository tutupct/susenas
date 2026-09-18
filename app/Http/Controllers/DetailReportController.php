<?php

namespace App\Http\Controllers;

use App\Models\DetailReport;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function update(
        Request $request,
        Report $report,
        DetailReport $detailReport
    ) {
        /*
     * Pastikan detail report memang milik report
     * yang sedang diedit.
     *
     * Ini penting untuk mencegah manipulasi URL/ID.
     */
        abort_unless(
            $detailReport->report_id === $report->id,
            404
        );

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

        $oldFotoPath = $detailReport->foto;
        $newFotoPath = null;

        /*
        * Upload foto baru hanya jika user memilih file.
        */
        if ($request->hasFile('foto')) {
            $newFotoPath = $request->file('foto')
                ->store("detail-reports/{$report->id}", 'public');
        }

        try {
            DB::transaction(function () use (
                $detailReport,
                $validated,
                $newFotoPath
            ) {
                $detailReport->update([
                    'keterangan_error' => trim($validated['keterangan_error']),
                    'foto' => $newFotoPath ?? $detailReport->foto,
                ]);
            });
        } catch (\Throwable $e) {
            /*
            * Kalau database gagal setelah file baru berhasil disimpan,
            * hapus file baru agar tidak menjadi file yatim.
            */
            if ($newFotoPath) {
                Storage::disk('public')->delete($newFotoPath);
            }

            throw $e;
        }

        /*
        * Setelah database berhasil di-update,
        * foto lama baru boleh dihapus.
        */
        if ($newFotoPath && $oldFotoPath) {
            Storage::disk('public')->delete($oldFotoPath);
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Temuan berhasil diperbarui.');
    }

    public function destroy(Report $report, DetailReport $detailReport)
    {
        /*
        * Pastikan DetailReport memang milik Report
        * yang dikirim di URL.
        */
        abort_unless(
            $detailReport->report_id === $report->id,
            404
        );

        /*
        * Hanya temuan berstatus draft yang boleh dihapus.
        */
        abort_unless(
            $detailReport->status === DetailReport::STATUS_DRAFT,
            403
        );

        $fotoPath = $detailReport->foto;

        $detailReport->delete();

        /*
        * Hapus file foto setelah record berhasil dihapus.
        */
        if ($fotoPath) {
            Storage::disk('public')->delete($fotoPath);
        }

        return redirect()
            ->route('reports.show', $report)
            ->with('success', 'Temuan berhasil dihapus.');
    }
}
