@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Daftar Report</h1>
            <p class="text-muted mb-0">
                Daftar KRT yang sedang divalidasi.
            </p>
        </div>

        <a href="{{ route('reports.create') }}" class="btn btn-primary">
            + Tambah Report
        </a>
    </div>

    @forelse ($reportGroups as $reports)
        @php
            $petugas = $reports->first()->petugas;
            $totalTemuan = $reports->sum('detail_reports_count');
        @endphp

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">

                {{-- Header Petugas --}}
                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">

                    <div>
                        <h2 class="h5 mb-1">
                            {{ $petugas->nama }}
                        </h2>

                        <div class="text-muted">
                            SLS: {{ $petugas->sls }}
                        </div>

                        <div class="small text-muted mt-1">
                            {{ $reports->count() }} KRT
                            ·
                            {{ $totalTemuan }} temuan
                        </div>
                    </div>

                    <a href="{{ route('reports.edit', $petugas) }}" class="btn btn-sm btn-outline-secondary">
                        Edit KRT
                    </a>

                </div>

                {{-- Daftar KRT --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th width="80">Urutan</th>
                                <th>Nama KRT</th>
                                <th width="120">Temuan</th>
                                <th width="100">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($reports as $report)
                                <tr>
                                    <td>
                                        {{ $report->urutan }}
                                    </td>

                                    <td>
                                        <span class="fw-semibold">
                                            {{ $report->nama_krt }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($report->detail_reports_count > 0)
                                            <span class="badge bg-danger">
                                                {{ $report->detail_reports_count }} temuan
                                            </span>
                                        @else
                                            <span class="badge bg-success">
                                                Tidak ada
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ route('reports.show', $report) }}"
                                            class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    @empty

        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">

                <div class="text-muted">
                    Belum ada report.
                </div>

                <a href="{{ route('reports.create') }}" class="btn btn-primary btn-sm mt-3">
                    + Tambah Report
                </a>

            </div>
        </div>
    @endforelse
@endsection
