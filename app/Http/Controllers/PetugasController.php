<?php

namespace App\Http\Controllers;

use App\Models\Petugas;
use Illuminate\Http\Request;

class PetugasController extends Controller
{
    public function index()
    {
        return view('petugas.index', [
            'petugas' => Petugas::orderBy('nama')->get()
        ]);
    }

    public function create()
    {
        return view('petugas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'min:3',
                'max:150',
                'regex:/^[\pL\pM\s\'.-]+$/u',
            ],

            'no_wa' => [
                'required',
                'string',
                'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/',
                'unique:petugas,no_wa',
            ],

            'sls' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[\pL\pM0-9\s\/._-]+$/u',
            ],
        ], [
            'nama.required' => 'Nama petugas wajib diisi.',
            'nama.min' => 'Nama petugas minimal 3 karakter.',
            'nama.max' => 'Nama petugas maksimal 150 karakter.',
            'nama.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, tanda kutip, dan tanda hubung.',

            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Format nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'no_wa.unique' => 'Nomor WhatsApp tersebut sudah terdaftar.',

            'sls.required' => 'SLS wajib diisi.',
            'sls.max' => 'SLS maksimal 100 karakter.',
            'sls.regex' => 'Format SLS tidak valid.',
        ]);

        /*
     * Normalisasi nomor WhatsApp
     * 081234567890 -> 6281234567890
     * +6281234567890 -> 6281234567890
     */
        $noWa = preg_replace('/\D+/', '', $validated['no_wa']);

        if (str_starts_with($noWa, '0')) {
            $noWa = '62' . substr($noWa, 1);
        } elseif (str_starts_with($noWa, '8')) {
            $noWa = '62' . $noWa;
        }

        // Cek ulang setelah normalisasi untuk mencegah duplikasi
        if (Petugas::where('no_wa', $noWa)->exists()) {
            return back()
                ->withInput()
                ->withErrors([
                    'no_wa' => 'Nomor WhatsApp tersebut sudah terdaftar.',
                ]);
        }

        Petugas::create([
            'nama' => trim($validated['nama']),
            'no_wa' => $noWa,
            'sls' => trim($validated['sls']),
        ]);

        return redirect()
            ->route('petugas.index')
            ->with('success', 'Petugas berhasil ditambahkan.');
    }

    public function edit(Petugas $petuga)
    {
        return view('petugas.edit', [
            'petugas' => $petuga
        ]);
    }

    public function update(Request $request, Petugas $petuga)
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'min:3',
                'max:150',
                'regex:/^[\pL\pM\s\'.-]+$/u',
            ],

            'no_wa' => [
                'required',
                'string',
                'regex:/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/',
            ],

            'sls' => [
                'required',
                'string',
                'min:1',
                'max:100',
                'regex:/^[\pL\pM0-9\s\/._-]+$/u',
            ],
        ], [
            'nama.required' => 'Nama petugas wajib diisi.',
            'nama.min' => 'Nama petugas minimal 3 karakter.',
            'nama.max' => 'Nama petugas maksimal 150 karakter.',
            'nama.regex' => 'Nama hanya boleh berisi huruf, spasi, titik, tanda kutip, dan tanda hubung.',

            'no_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'no_wa.regex' => 'Format nomor WhatsApp tidak valid. Contoh: 081234567890.',

            'sls.required' => 'SLS wajib diisi.',
            'sls.max' => 'SLS maksimal 100 karakter.',
            'sls.regex' => 'Format SLS tidak valid.',
        ]);

        /*
     * Normalisasi nomor WhatsApp
     *
     * 081234567890   -> 6281234567890
     * 621234567890   -> 621234567890
     * +6281234567890 -> 6281234567890
     */
        $noWa = preg_replace('/\D+/', '', $validated['no_wa']);

        if (str_starts_with($noWa, '0')) {
            $noWa = '62' . substr($noWa, 1);
        } elseif (str_starts_with($noWa, '8')) {
            $noWa = '62' . $noWa;
        }

        /*
     * Cek apakah nomor WA sudah digunakan petugas lain.
     * ID petugas yang sedang diedit dikecualikan.
     */
        $existingPetugas = Petugas::where('no_wa', $noWa)
            ->where('id', '!=', $petuga->id)
            ->exists();

        if ($existingPetugas) {
            return back()
                ->withInput()
                ->withErrors([
                    'no_wa' => 'Nomor WhatsApp tersebut sudah digunakan oleh petugas lain.',
                ]);
        }

        $petuga->update([
            'nama' => trim($validated['nama']),
            'no_wa' => $noWa,
            'sls' => trim($validated['sls']),
        ]);

        return redirect()
            ->route('petugas.index')
            ->with('success', 'Data petugas berhasil diperbarui.');
    }
    public function destroy(Petugas $petuga)
    {
        // if ($petuga->reports()->exists()) {
        //     return redirect()
        //         ->route('petugas.index')
        //         ->with('error', 'Petugas tidak dapat dihapus karena sudah memiliki report.');
        // }

        $petuga->delete();

        return redirect()
            ->route('petugas.index')
            ->with('success', 'Petugas berhasil dihapus.');
    }
}
