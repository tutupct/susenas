<?php

namespace App\Http\Controllers;

use App\Models\DetailReport;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\GowaService;

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

    public function update(Request $request, Report $report, DetailReport $detailReport)
    {
        abort_unless($detailReport->report_id === $report->id, 404);
        abort_unless($detailReport->status === DetailReport::STATUS_DRAFT, 403, 'Temuan yang sudah dikirim tidak dapat diedit.');

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

    public function sendToWhatsApp(GowaService $gowa, Report $report, DetailReport $detailReport)
    {
        /*
        * Pastikan detail report memang milik report
        * yang dikirim di URL.
        */
        abort_unless($detailReport->report_id === $report->id, 404);

        /*
        * Untuk sekarang, hanya temuan draft yang boleh dikirim.
        */
        abort_unless($detailReport->status === DetailReport::STATUS_DRAFT, 403, 'Temuan sudah tidak berstatus draft.');

        /*
        * Ambil petugas beserta nomor WhatsApp-nya.
        */
        $report->loadMissing('petugas');

        $petugas = $report->petugas;

        abort_unless($petugas && $petugas->no_wa, 422, 'Petugas belum memiliki nomor WhatsApp.');

        /*
        * Susun pesan/caption yang akan dikirim.
        */
        $message = implode("\n", [
            "Halo {$petugas->nama},",
            "",
            "Ada temuan pada data KRT: {$report->nama_krt}.",
            "",
            "Temuan:",
            $detailReport->keterangan_error,
        ]);

        try {
            /*
            * Kalau ada foto, kirim sebagai gambar
            * dengan message sebagai caption.
            */
            if ($detailReport->foto) {
                $imagePath = Storage::disk('public')->path(
                    $detailReport->foto
                );

                $response = $gowa->sendImage(
                    $petugas->no_wa,
                    $imagePath,
                    $message
                );
            } else {
                /*
                * Kalau tidak ada foto, kirim pesan teks biasa.
                */
                $response = $gowa->sendMessage(
                    $petugas->no_wa,
                    $message
                );
            }

            /*
            * GOWA harus memberikan response sukses
            * sebelum status temuan diubah.
            */
            if (! $response->successful()) {
                throw new \RuntimeException(
                    'GOWA gagal mengirim pesan: ' . $response->body()
                );
            }

            $detailReport->update([
                'status' => DetailReport::STATUS_TERKIRIM,
            ]);
        } catch (\Throwable $e) {
            return back()->with(
                'error',
                'Pesan WhatsApp gagal dikirim: ' . $e->getMessage()
            );
        }

        return back()->with('success', 'Temuan berhasil dikirim ke WhatsApp petugas.');
    }

    public function recordResponse(Request $request, Report $report, DetailReport $detailReport)
    {
        abort_unless(
            $detailReport->report_id === $report->id,
            404
        );

        abort_unless(
            in_array(
                $detailReport->status,
                [
                    DetailReport::STATUS_TERKIRIM,
                    DetailReport::STATUS_DIPERBAIKI,
                ],
                true
            ),
            403,
            'Respons petugas belum dapat dicatat untuk temuan ini.'
        );

        $validated = $request->validate([
            'respon_petugas' => [
                'required',
                'string',
                'min:1',
                'max:5000',
            ],
        ], [
            'respon_petugas.required' => 'Respons petugas wajib diisi.',
            'respon_petugas.max' => 'Respons petugas maksimal 5000 karakter.',
        ]);

        $detailReport->update([
            'respon_petugas' => trim($validated['respon_petugas']),
            'status' => DetailReport::STATUS_DIPERBAIKI,
        ]);

        return back()->with(
            'success',
            'Respons petugas berhasil dicatat dan temuan ditandai sebagai diperbaiki.'
        );
    }
}
