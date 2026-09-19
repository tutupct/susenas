@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total KRT</div>
                    <div class="fs-4 fw-semibold">{{ number_format($stats['total_krt']) }}</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">KRT Ada Temuan</div>
                    <div class="fs-4 fw-semibold text-danger">
                        {{ number_format($stats['krt_with_findings']) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Temuan</div>
                    <div class="fs-4 fw-semibold">
                        {{ number_format($stats['total_findings']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.index') }}">
                <div class="row g-2">
                    <div class="col-md-7">
                        <label for="search" class="visually-hidden">Cari</label>
                        <input
                            type="search"
                            id="search"
                            name="q"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Cari nama KRT, petugas, atau SLS..."
                        >
                    </div>

                    <div class="col-md-3">
                        <label for="filter" class="visually-hidden">Filter temuan</label>
                        <select id="filter" name="filter" class="form-select">
                            <option value="all" @selected($filter === 'all')>Semua KRT</option>
                            <option value="with_findings" @selected($filter === 'with_findings')>Ada temuan</option>
                            <option value="without_findings" @selected($filter === 'without_findings')>Belum ada temuan</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            Cari
                        </button>

                        @if ($search !== '' || $filter !== 'all')
                            <a href="{{ route('reports.index') }}" class="btn btn-light" title="Reset filter">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @forelse ($reportGroups as $reports)
        @php
            $petugas = $reports->first()->petugas;
            $totalTemuan = $reports->sum('detail_reports_count');
            $krtDenganTemuan = $reports->where('detail_reports_count', '>', 0)->count();
            $groupId = 'petugas-' . $petugas->id;
        @endphp

        <div class="card border-0 shadow-sm mb-3" x-data="{ open: true }">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h2 class="h5 mb-0">{{ $petugas->nama }}</h2>

                            @if ($krtDenganTemuan > 0)
                                <span class="badge text-bg-danger">
                                    {{ $krtDenganTemuan }} KRT perlu dicek
                                </span>
                            @else
                                <span class="badge text-bg-success">Tidak ada temuan</span>
                            @endif
                        </div>

                        <div class="text-muted small mt-1">
                            SLS: {{ $petugas->sls }}
                            · {{ $reports->count() }} KRT
                            · {{ $totalTemuan }} temuan
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('reports.edit', $petugas) }}"
                            class="btn btn-sm btn-outline-secondary">
                            Edit KRT
                        </a>

                        <button
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-controls="{{ $groupId }}"
                            x-text="open ? 'Tutup' : 'Lihat'"
                        ></button>
                    </div>
                </div>

                <div id="{{ $groupId }}" x-show="open" class="mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="80">Urutan</th>
                                    <th>Nama KRT</th>
                                    <th width="140">Temuan</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($reports as $report)
                                    <tr>
                                        <td>{{ $report->urutan }}</td>

                                        <td>
                                            <span class="fw-semibold">{{ $report->nama_krt }}</span>
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
        </div>
    @empty
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                @if ($search !== '' || $filter !== 'all')
                    <div class="text-muted">Tidak ada KRT yang sesuai dengan filter.</div>
                    <a href="{{ route('reports.index') }}" class="btn btn-light btn-sm mt-3">
                        Reset Filter
                    </a>
                @else
                    <div class="text-muted">Belum ada report.</div>
                    <a href="{{ route('reports.create') }}" class="btn btn-primary btn-sm mt-3">
                        + Tambah Report
                    </a>
                @endif
            </div>
        </div>
    @endforelse
@endsection
