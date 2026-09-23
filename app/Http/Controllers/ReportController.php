<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Http\Requests\UpdateReportRequest;
use App\Models\DetailReport;
use App\Models\Petugas;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],
            'filter' => [
                'nullable',
                Rule::in([
                    'all',
                    'with_findings',
                    'without_findings',
                ]),
            ],
        ]);

        $search = trim($validated['q'] ?? '');
        $filter = $validated['filter'] ?? 'all';

        $reportsQuery = Report::query()
            ->with('petugas')
            ->withCount('detailReports')
            ->withCount([
                'detailReports as draft_count' => fn($query) => $query->where('status', DetailReport::STATUS_DRAFT),
                'detailReports as terkirim_count' => fn($query) => $query->where('status', DetailReport::STATUS_TERKIRIM),
                'detailReports as diperbaiki_count' => fn($query) => $query->where('status', DetailReport::STATUS_DIPERBAIKI),
                'detailReports as selesai_count' => fn($query) => $query->where('status', DetailReport::STATUS_SELESAI),
            ])
            ->orderBy('petugas_id')
            ->orderBy('urutan');

        if ($search !== '') {
            $reportsQuery->where(function ($query) use ($search) {
                $query
                    ->where('nama_krt', 'like', "%{$search}%")
                    ->orWhereHas('petugas', function ($petugasQuery) use ($search) {
                        $petugasQuery
                            ->where('nama', 'like', "%{$search}%")
                            ->orWhere('sls', 'like', "%{$search}%");
                    });
            });
        }

        match ($filter) {
            'with_findings' => $reportsQuery->has('detailReports'),
            'without_findings' => $reportsQuery->doesntHave('detailReports'),
            default => null,
        };

        $reports = $reportsQuery->get();

        $stats = [
            'total_krt' => Report::count(),
            'krt_with_findings' => Report::has('detailReports')->count(),
            'total_findings' => DetailReport::count(),
        ];

        return view('reports.index', compact(
            'reports',
            'search',
            'filter',
            'stats',
        ));
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
        if ($report->detailReports()->exists()) {
            return back()
                ->withErrors([
                    'report' => "KRT {$report->nama_krt} tidak dapat dihapus karena sudah memiliki temuan.",
                ]);
        }

        $report->delete();

        return redirect()
            ->route('reports.index')
            ->with('success', "Report KRT {$report->nama_krt} berhasil dihapus.");
    }
}
