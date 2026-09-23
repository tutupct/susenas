@extends('layouts.app')

@push('styles')
    <style>
        .reports-summary {
            list-style: none;
        }

        .reports-summary::-webkit-details-marker {
            display: none;
        }

        .reports-summary-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: .35rem;
        }

        .reports-summary-meta .badge {
            font-weight: 500;
        }

        .reports-petugas {
            margin: .75rem;
            overflow: hidden;
            border: 1px solid var(--bs-border-color) !important;
            border-radius: .5rem;
            background-color: var(--bs-body-bg);
        }

        @media (max-width: 575.98px) {
            .reports-petugas {
                margin: .5rem;
            }

            .reports-summary {
                align-items: flex-start !important;
                flex-direction: column;
                padding: 1rem !important;
            }

            .reports-summary-meta {
                justify-content: flex-start;
            }

            .reports-table thead {
                display: none;
            }

            .reports-table tbody tr {
                display: grid;
                grid-template-columns: 1fr auto;
                gap: .25rem .75rem;
                padding: .85rem 1rem;
                border-bottom: 1px solid var(--bs-border-color);
            }

            .reports-table tbody tr:last-child {
                border-bottom: 0;
            }

            .reports-table tbody td {
                border: 0;
                padding: 0;
            }

            .reports-table .report-order {
                color: var(--bs-secondary-color);
                font-size: .8rem;
                grid-column: 1 / -1;
            }

            .reports-table .report-krt {
                grid-column: 1 / -1;
            }

            .reports-table .report-count {
                align-self: center;
            }

            .reports-table .report-status {
                grid-column: 1 / -1;
                margin-top: .35rem;
            }

            .reports-table .report-action {
                grid-column: 1 / -1;
                padding: .35rem 0 0;
                text-align: right;
            }

            .reports-table .report-action .btn {
                width: 100%;
            }

            .reports-table .report-action>div {
                flex-direction: column;
            }

            .reports-table .report-action form {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Daftar KRT</h1>
            <p class="text-muted mb-0">Pantau temuan per KRT dan lanjutkan validasi dari sini.</p>
        </div>

        <a href="{{ route('reports.create') }}" class="btn btn-primary">
            + Tambah Report
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total KRT</div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_krt']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">KRT Ada Temuan</div>
                    <div class="fs-4 fw-semibold text-danger">{{ number_format($stats['krt_with_findings']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Total Temuan</div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_findings']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}">
                <div class="row g-2">
                    <div class="col-md-7">
                        <input type="search" name="q" value="{{ $search }}" class="form-control"
                            placeholder="Cari nama KRT, petugas, atau SLS..." aria-label="Cari KRT">
                    </div>

                    <div class="col-md-3">
                        <select name="filter" class="form-select" aria-label="Filter temuan">
                            <option value="all" @selected($filter === 'all')>Semua</option>
                            <option value="with_findings" @selected($filter === 'with_findings')>Ada temuan</option>
                            <option value="without_findings" @selected($filter === 'without_findings')>Belum ada temuan</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Cari</button>

                        @if ($search !== '' || $filter !== 'all')
                            <a href="{{ route('reports.index') }}" class="btn btn-light">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @php
        $reportsByPetugas = $reports->groupBy('petugas_id');
    @endphp

    <div class="card border-0 shadow-sm">
        @forelse ($reportsByPetugas as $petugasReports)
            @php
                $petugas = $petugasReports->first()->petugas;
                $totalTemuan = $petugasReports->sum('detail_reports_count');
                $statusCounts = [
                    'draft' => $petugasReports->sum('draft_count'),
                    'terkirim' => $petugasReports->sum('terkirim_count'),
                    'diperbaiki' => $petugasReports->sum('diperbaiki_count'),
                    'selesai' => $petugasReports->sum('selesai_count'),
                ];
            @endphp

            <details class="reports-petugas">
                <summary class="reports-summary d-flex justify-content-between align-items-center gap-3 px-4 py-3"
                    style="cursor: pointer;">
                    <div>
                        <div class="fw-semibold">{{ $petugas->nama }}</div>
                        <div class="text-muted small">SLS: {{ $petugas->sls }}</div>
                    </div>

                    <div class="reports-summary-meta text-end">
                        <span class="badge text-bg-light">
                            {{ $petugasReports->count() }} KRT
                        </span>

                        @if ($totalTemuan > 0)
                            <span class="badge text-bg-danger">
                                {{ $totalTemuan }} temuan
                            </span>

                            @if ($statusCounts['draft'] > 0)
                                <span class="badge text-bg-secondary">{{ $statusCounts['draft'] }} draft</span>
                            @endif

                            @if ($statusCounts['terkirim'] > 0)
                                <span class="badge text-bg-warning">{{ $statusCounts['terkirim'] }} terkirim</span>
                            @endif

                            @if ($statusCounts['diperbaiki'] > 0)
                                <span class="badge text-bg-info">{{ $statusCounts['diperbaiki'] }} diperbaiki</span>
                            @endif

                            @if ($statusCounts['selesai'] > 0)
                                <span class="badge text-bg-success">{{ $statusCounts['selesai'] }} selesai</span>
                            @endif
                        @endif
                    </div>
                </summary>

                <div class="d-flex justify-content-end px-3 px-md-4 py-2 border-top bg-light">
                    <a href="{{ route('reports.edit', $petugas) }}" class="btn btn-sm btn-outline-secondary">
                        Edit Report
                    </a>
                </div>

                <div class="table-responsive border-top">
                    <table class="reports-table table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" width="80">Urutan</th>
                                <th>KRT</th>
                                <th width="130">Temuan</th>
                                <th width="270">Status Temuan</th>
                                <th width="100" class="pe-4">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($petugasReports as $report)
                                <tr>
                                    <td class="report-order ps-4">
                                        <span class="fw-semibold">{{ $report->urutan }}</span>
                                    </td>

                                    <td class="report-krt">
                                        <div class="fw-semibold">{{ $report->nama_krt }}</div>
                                    </td>

                                    <td class="report-count">
                                        @if ($report->detail_reports_count > 0)
                                            <span class="badge text-bg-danger">
                                                {{ $report->detail_reports_count }} temuan
                                            </span>
                                        @else
                                            <span class="text-muted small">Tidak ada</span>
                                        @endif
                                    </td>

                                    <td class="report-status">
                                        @if ($report->detail_reports_count > 0)
                                            <div class="d-flex flex-wrap gap-1">
                                                @if ($report->draft_count > 0)
                                                    <span class="badge text-bg-secondary">{{ $report->draft_count }}
                                                        draft</span>
                                                @endif

                                                @if ($report->terkirim_count > 0)
                                                    <span class="badge text-bg-warning">{{ $report->terkirim_count }}
                                                        terkirim</span>
                                                @endif

                                                @if ($report->diperbaiki_count > 0)
                                                    <span class="badge text-bg-info">{{ $report->diperbaiki_count }}
                                                        diperbaiki</span>
                                                @endif

                                                @if ($report->selesai_count > 0)
                                                    <span class="badge text-bg-success">{{ $report->selesai_count }}
                                                        selesai</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted small">Belum ada temuan</span>
                                        @endif
                                    </td>

                                    <td class="report-action pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('reports.show', $report) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>

                                            @if ($report->detail_reports_count === 0)
                                                <form action="{{ route('reports.destroy', $report) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus report KRT ini?');">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @empty
            <div class="text-center py-5 text-muted">
                @if ($search !== '' || $filter !== 'all')
                    Tidak ada KRT yang sesuai dengan filter.
                @else
                    Belum ada report.
                @endif
            </div>
        @endforelse

        @if ($reports->isNotEmpty())
            <div class="card-footer bg-white border-top text-muted small">
                Menampilkan {{ $reports->count() }} KRT
                @if ($search !== '' || $filter !== 'all')
                    sesuai filter.
                @else
                    .
                @endif
            </div>
        @endif
    </div>
@endsection
