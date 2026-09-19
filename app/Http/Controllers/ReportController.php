<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\Petugas;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('petugas')
            ->withCount('detailReports')
            ->orderBy('petugas_id')
            ->orderBy('urutan')
            ->get();

        $reportGroups = $reports->groupBy('petugas_id');

        return view('reports.index', compact('reportGroups'));
    }

    public function create()
    {
        return view('reports.manage', [
            'mode' => 'create',
            'petugas' => Petugas::whereDoesntHave('reports')
                ->orderBy('nama')
                ->get(),
            'selectedPetugas' => null,
            'initialRows' => [],
        ]);
    }

    public function store(StoreReportRequest $request)
    {
        $validated = $request->validated();

        $petugas = Petugas::findOrFail($validated['petugas_id']);

        if (Report::where('petugas_id', $petugas->id)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'petugas_id' => 'Petugas ini sudah memiliki daftar KRT.',
                ]);
        }

        /*
        * Pastikan urutan tidak duplikat
        */
        $urutan = collect($validated['reports'])
            ->pluck('urutan')
            ->map(fn($value) => (int) $value)
            ->sort()
            ->values()
            ->all();

        $expected = range(1, count($urutan));

        if ($urutan !== $expected) {
            return back()
                ->withInput()
                ->withErrors([
                    'reports' => 'Urutan KRT harus berurutan mulai dari 1 tanpa duplikat atau lompatan.',
                ]);
        }

        /*
        * Simpan seluruh report dalam satu transaksi.
        * Kalau satu gagal, semuanya dibatalkan.
        */
        DB::transaction(function () use ($validated, $petugas) {
            foreach ($validated['reports'] as $item) {
                Report::create([
                    'petugas_id' => $petugas->id,
                    'urutan' => $item['urutan'],
                    'nama_krt' => trim($item['nama_krt']),
                ]);
            }
        });

        return redirect()->route('reports.index')->with('success', count($validated['reports']) . ' data KRT untuk petugas ' . $petugas->nama . ' berhasil disimpan.');
    }

    public function show(Report $report)
    {
        $report->load([
            'petugas',
            'detailReports',
        ]);

        return view('reports.show', compact('report'));
    }

    public function edit(Petugas $petugas)
    {
        $reports = $petugas->reports()
            ->orderBy('urutan')
            ->get();

        if ($reports->isEmpty()) {
            abort(404);
        }

        $initialRows = $reports->map(function ($report) {
            return [
                'id' => $report->id,
                'nama_krt' => $report->nama_krt,
            ];
        })->values()->all();

        return view('reports.manage', [
            'mode' => 'edit',
            'petugas' => collect([$petugas]),
            'selectedPetugas' => $petugas,
            'initialRows' => $initialRows,
        ]);
    }

    public function update(UpdateReportRequest $request, Petugas $petugas)
    {
        $validated = $request->validated();

        $existingReports = $petugas->reports()
            ->withCount('detailReports')
            ->get()
            ->keyBy('id');

        $submittedIds = collect($validated['reports'])
            ->pluck('id')
            ->filter()
            ->map(fn($id) => (int) $id);

        /*
     * Pastikan ID report yang dikirim memang milik petugas ini.
     */
        if ($submittedIds->diff($existingReports->keys())->isNotEmpty()) {
            abort(403, 'Report tidak valid.');
        }

        /*
     * Pastikan urutan 1, 2, 3, ..., N.
     */
        $urutan = collect($validated['reports'])
            ->pluck('urutan')
            ->map(fn($value) => (int) $value)
            ->sort()
            ->values()
            ->all();

        $expected = range(1, count($urutan));

        if ($urutan !== $expected) {
            return back()
                ->withInput()
                ->withErrors([
                    'reports' => 'Urutan KRT harus berurutan mulai dari 1 tanpa duplikat atau lompatan.',
                ]);
        }

        /*
     * Cari report yang dihapus dari form.
     */
        $deletedIds = $existingReports->keys()
            ->diff($submittedIds);

        /*
     * Report yang sudah punya detail tidak boleh dihapus.
     */
        $cannotDelete = $existingReports
            ->only($deletedIds->all())
            ->filter(fn($report) => $report->detail_reports_count > 0);

        if ($cannotDelete->isNotEmpty()) {
            $nama = $cannotDelete
                ->pluck('nama_krt')
                ->join(', ');

            return back()
                ->withInput()
                ->withErrors([
                    'reports' => "KRT berikut tidak dapat dihapus karena sudah memiliki temuan: {$nama}.",
                ]);
        }

        DB::transaction(function () use (
            $validated,
            $petugas,
            $existingReports,
            $submittedIds,
            $deletedIds
        ) {

            /*
         * Hapus report yang memang dihapus dari form.
         */
            if ($deletedIds->isNotEmpty()) {
                $petugas->reports()
                    ->whereIn('id', $deletedIds->all())
                    ->delete();
            }

            /*
         * Kosongkan sementara urutan report lama
         * agar tidak bentrok dengan unique(petugas_id, urutan).
         */
            foreach ($submittedIds as $id) {
                $report = $existingReports->get($id);

                if ($report) {
                    $report->update([
                        'urutan' => -$report->id,
                    ]);
                }
            }

            /*
         * Update report lama / create report baru.
         */
            foreach ($validated['reports'] as $item) {

                if (!empty($item['id'])) {

                    $report = $existingReports->get((int) $item['id']);

                    $report->update([
                        'urutan' => $item['urutan'],
                        'nama_krt' => trim($item['nama_krt']),
                    ]);
                } else {

                    $petugas->reports()->create([
                        'urutan' => $item['urutan'],
                        'nama_krt' => trim($item['nama_krt']),
                    ]);
                }
            }
        });

        return redirect()
            ->route('reports.index')
            ->with(
                'success',
                'Daftar KRT untuk petugas ' . $petugas->nama . ' berhasil diperbarui.'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
