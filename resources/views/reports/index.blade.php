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

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Petugas</th>
                            <th>SLS</th>
                            <th width="80">Urutan</th>
                            <th>Nama KRT</th>
                            <th width="100">Temuan</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($reports as $report)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        {{ $report->petugas->nama }}
                                    </span>
                                </td>

                                <td>
                                    {{ $report->petugas->sls }}
                                </td>

                                <td>
                                    {{ $report->urutan }}
                                </td>

                                <td>
                                    {{ $report->nama_krt }}
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
                                    <a href="{{ route('reports.edit', $report->petugas) }}"
                                        class="btn btn-sm btn-outline-secondary">
                                        Edit
                                    </a>

                                    <a href="{{ route('reports.show', $report) }}" class="btn btn-sm btn-outline-primary">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="text-muted">
                                        Belum ada report.
                                    </div>

                                    <a href="{{ route('reports.create') }}" class="btn btn-primary btn-sm mt-3">
                                        + Tambah Report
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>

    </div>
@endsection
