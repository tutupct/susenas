@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Daftar Petugas</h1>
            <p class="text-muted mb-0">
                Kelola data petugas lapangan.
            </p>
        </div>

        <a href="{{ route('petugas.create') }}" class="btn btn-primary">
            + Tambah Petugas
        </a>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Nama</th>
                            <th>No. WhatsApp</th>
                            <th>SLS</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($petugas as $item)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <span class="fw-semibold">
                                        {{ $item->nama }}
                                    </span>
                                </td>

                                <td>
                                    {{ $item->no_wa }}
                                </td>

                                <td>
                                    {{ $item->sls }}
                                </td>

                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('petugas.edit', $item->id) }}" class="btn btn-sm btn-outline-primary">
                                            Edit
                                        </a>

                                        <form action="{{ route('petugas.destroy', $item->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Hapus petugas ini?')">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        Belum ada data petugas.
                                    </div>

                                    <a href="{{ route('petugas.create') }}" class="btn btn-primary btn-sm mt-3">
                                        + Tambah Petugas
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
