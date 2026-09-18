<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use App\Models\Report;
use Illuminate\Http\Request;
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

        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.manage', [
            'petugas' => Petugas::whereDoesntHave('reports')
                ->orderBy('nama')
                ->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
        ], [
            'petugas_id.required' => 'Petugas wajib dipilih.',
            'petugas_id.exists' => 'Petugas yang dipilih tidak valid.',

            'reports.required' => 'Minimal satu KRT harus diisi.',
            'reports.min' => 'Minimal satu KRT harus diisi.',

            'reports.*.nama_krt.required' => 'Nama KRT wajib diisi.',
            'reports.*.nama_krt.min' => 'Nama KRT minimal 2 karakter.',
            'reports.*.nama_krt.max' => 'Nama KRT maksimal 150 karakter.',

            'reports.*.urutan.required' => 'Urutan KRT wajib diisi.',
            'reports.*.urutan.min' => 'Urutan KRT tidak valid.',
        ]);

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
            ->sort()
            ->values()
            ->all();

        $expected = range(1, count($urutan));

        if ($urutan != $expected) {
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

        return redirect()
            ->route('reports.index')
            ->with(
                'success',
                count($validated['reports']) .
                    ' data KRT untuk petugas ' .
                    $petugas->nama .
                    ' berhasil disimpan.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        //
    }
}
