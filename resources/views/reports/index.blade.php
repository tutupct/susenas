@extends('layouts.app')

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
            @endphp

            <details class="border-bottom">
                <summary class="d-flex justify-content-between align-items-center gap-3 px-4 py-3"
                    style="cursor: pointer; list-style: none;">
                    <div>
                        <div class="fw-semibold">{{ $petugas->nama }}</div>
                        <div class="text-muted small">SLS: {{ $petugas->sls }}</div>
                    </div>

                    <div class="text-end text-nowrap">
                        <span class="badge text-bg-light">
                            {{ $petugasReports->count() }} KRT
                        </span>

                        @if ($totalTemuan > 0)
                            <span class="badge text-bg-danger">
                                {{ $totalTemuan }} temuan
                            </span>
                        @endif
                    </div>
                </summary>

                <div class="table-responsive border-top">
                    <table class="table table-hover align-middle mb-0">
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
                                    <td class="ps-4">
                                        <span class="fw-semibold">{{ $report->urutan }}</span>
                                    </td>

                                    <td>
                                        <div class="fw-semibold">{{ $report->nama_krt }}</div>
                                    </td>

                                    <td>
                                        @if ($report->detail_reports_count > 0)
                                            <span class="badge text-bg-danger">
                                                {{ $report->detail_reports_count }} temuan
                                            </span>
                                        @else
                                            <span class="text-muted small">Tidak ada</span>
                                        @endif
                                    </td>

                                    <td>
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

                                    <td class="pe-4">
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
