@extends('layouts.app')

@section('content')
    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('reports.index') }}" class="text-decoration-none">
            ← Kembali ke Daftar Report
        </a>

        <div class="mt-3">
            <h1 class="h3 mb-1">Detail KRT</h1>
            <p class="text-muted mb-0">
                Informasi KRT dan temuan validasi.
            </p>
        </div>
    </div>

    {{-- Informasi KRT --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <h2 class="h5 mb-3">
                Informasi KRT
            </h2>

            <div class="row g-3">

                <div class="col-md-6">
                    <div class="text-muted small">
                        Petugas
                    </div>

                    <div class="fw-semibold">
                        {{ $report->petugas->nama }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">
                        SLS
                    </div>

                    <div class="fw-semibold">
                        {{ $report->petugas->sls }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">
                        Urutan KRT
                    </div>

                    <div class="fw-semibold">
                        {{ $report->urutan }}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">
                        Nama KRT
                    </div>

                    <div class="fw-semibold">
                        {{ $report->nama_krt }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- Temuan --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h5 mb-1">
                Temuan
            </h2>

            <div class="text-muted small">
                {{ $report->detailReports->count() }} temuan
            </div>
        </div>
    </div>

    @forelse ($report->detailReports as $detail)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                    <h3 class="h6 mb-0">
                        Temuan #{{ $loop->iteration }}
                    </h3>

                    @switch($detail->status)
                        @case(\App\Models\DetailReport::STATUS_DRAFT)
                            <span class="badge bg-secondary">
                                Draft
                            </span>
                        @break

                        @case(\App\Models\DetailReport::STATUS_TERKIRIM)
                            <span class="badge bg-primary">
                                Terkirim
                            </span>
                        @break

                        @case(\App\Models\DetailReport::STATUS_DIPERBAIKI)
                            <span class="badge bg-warning text-dark">
                                Diperbaiki
                            </span>
                        @break

                        @case(\App\Models\DetailReport::STATUS_SELESAI)
                            <span class="badge bg-success">
                                Selesai
                            </span>
                        @break

                        @default
                            <span class="badge bg-secondary">
                                {{ $detail->status }}
                            </span>
                    @endswitch
                </div>

                {{-- Keterangan --}}
                <div class="mb-3">
                    <div class="text-muted small mb-1">
                        Keterangan
                    </div>

                    <div>
                        {!! nl2br(e($detail->keterangan_error)) !!}
                    </div>
                </div>

                {{-- Foto --}}
                <div>
                    <div class="text-muted small mb-2">
                        Foto Pendukung
                    </div>

                    @if ($detail->foto)
                        <img src="{{ asset('storage/' . $detail->foto) }}" alt="Foto pendukung"
                            class="img-fluid rounded border" style="max-height: 400px;">
                    @else
                        <div class="text-muted">
                            Belum ada foto pendukung.
                        </div>
                    @endif
                </div>

            </div>
        </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="text-muted">
                        Belum ada temuan untuk KRT ini.
                    </div>
                </div>
            </div>
        @endforelse
    @endsection
