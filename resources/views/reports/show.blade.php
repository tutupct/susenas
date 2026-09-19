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
        <div class="card border-0 shadow-sm mb-3" x-data="editDetailReportForm()">
            <div class="card-body">

                {{-- =========================
                MODE TAMPIL
            ========================== --}}
                <div x-show="!editing">

                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <h3 class="h6 mb-0">
                                Temuan #{{ $loop->iteration }}
                            </h3>
                        </div>

                        <div class="d-flex align-items-center gap-2">

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

                            @if ($detail->status === \App\Models\DetailReport::STATUS_DRAFT)
                                <button type="button" class="btn btn-sm btn-outline-secondary" @click="editing = true">
                                    Edit
                                </button>
                            @endif

                            @if ($detail->status === \App\Models\DetailReport::STATUS_DRAFT)
                                <form action="{{ route('reports.details.send', [$report, $detail]) }}" method="POST"
                                    class="d-inline" onsubmit="return confirm('Kirim temuan ini ke WhatsApp petugas?');">
                                    @csrf

                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        Kirim WA
                                    </button>
                                </form>

                                <form action="{{ route('reports.details.destroy', [$report, $detail]) }}" method="POST"
                                    class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus temuan ini? Foto pendukung juga akan dihapus.');">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        Hapus
                                    </button>
                                </form>
                            @endif

                        </div>
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
                    {{-- Respons Petugas --}}
                    <div class="mt-4" x-data="{ editingResponse: false }">

                        <div class="text-muted small mb-2">
                            Respons Petugas
                        </div>

                        {{-- TERKIRIM: form input respons --}}
                        @if ($detail->status === \App\Models\DetailReport::STATUS_TERKIRIM)
                            <form action="{{ route('reports.details.response', [$report, $detail]) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <textarea name="respon_petugas" class="form-control @error('respon_petugas') is-invalid @enderror" rows="4"
                                    placeholder="Masukkan jawaban atau data yang diberikan petugas..." required>{{ old('respon_petugas', $detail->respon_petugas) }}</textarea>

                                @error('respon_petugas')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        Simpan Respons
                                    </button>
                                </div>
                            </form>

                            {{-- DIPERBAIKI: tampilkan respons + bisa diedit --}}
                        @elseif ($detail->status === \App\Models\DetailReport::STATUS_DIPERBAIKI)
                            <div x-show="!editingResponse">
                                <div class="bg-light border rounded p-3">
                                    {!! nl2br(e($detail->respon_petugas)) !!}
                                </div>

                                <div class="d-flex justify-content-end mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        @click="editingResponse = true">
                                        Edit Respons
                                    </button>
                                </div>
                            </div>

                            <div x-show="editingResponse" x-cloak>
                                <form action="{{ route('reports.details.response', [$report, $detail]) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <textarea name="respon_petugas" class="form-control @error('respon_petugas') is-invalid @enderror" rows="4"
                                        required>{{ old('respon_petugas', $detail->respon_petugas) }}</textarea>

                                    @error('respon_petugas')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                    <div class="d-flex justify-content-end gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-light"
                                            @click="editingResponse = false">
                                            Batal
                                        </button>

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Simpan Perubahan
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- SELESAI --}}
                        @elseif ($detail->status === \App\Models\DetailReport::STATUS_SELESAI)
                            <div class="bg-light border rounded p-3">
                                {!! nl2br(e($detail->respon_petugas)) !!}
                            </div>
                        @endif

                    </div>

                </div>


                {{-- =========================
                MODE EDIT
            ========================== --}}
                <div x-show="editing" x-cloak>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="h6 mb-0">
                            Edit Temuan #{{ $loop->iteration }}
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

                    <form action="{{ route('reports.details.update', [$report, $detail]) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Keterangan --}}
                        <div class="mb-3">
                            <label for="keterangan_error_{{ $detail->id }}" class="form-label fw-semibold">
                                Keterangan Temuan
                            </label>

                            <textarea id="keterangan_error_{{ $detail->id }}" name="keterangan_error"
                                class="form-control @error('keterangan_error') is-invalid @enderror" rows="4" required>{{ $detail->keterangan_error }}</textarea>

                            @error('keterangan_error')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Foto saat ini --}}
                        @if ($detail->foto)
                            <div class="mb-3">
                                <div class="text-muted small mb-2">
                                    Foto Saat Ini
                                </div>

                                <img src="{{ asset('storage/' . $detail->foto) }}" alt="Foto pendukung saat ini"
                                    class="img-fluid rounded border" style="max-height: 300px;">
                            </div>
                        @endif

                        {{-- Foto baru --}}
                        <div class="mb-3">
                            <label for="foto_{{ $detail->id }}" class="form-label fw-semibold">
                                Ganti Foto
                            </label>

                            <input type="file" id="foto_{{ $detail->id }}" name="foto"
                                class="form-control @error('foto') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/webp" @change="handlePhotoChange($event)">

                            <div class="form-text">
                                Kosongkan jika tidak ingin mengganti foto.
                                JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.
                            </div>

                            @error('foto')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Preview foto baru --}}
                        <div x-show="previewUrl" x-cloak class="mb-3">
                            <div class="text-muted small mb-2">
                                Preview Foto Baru
                            </div>

                            <div class="position-relative d-inline-block">
                                <img :src="previewUrl" alt="Preview foto baru" class="img-fluid rounded border"
                                    style="max-height: 300px;">

                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                                    @click="removePhoto()" title="Batalkan foto baru">
                                    &times;
                                </button>
                            </div>
                        </div>

                        {{-- Tombol --}}
                        <div class="d-flex justify-content-end gap-2">

                            <button type="button" class="btn btn-light" @click="cancelEdit()">
                                Batal
                            </button>

                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

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

        <div class="card border-0 shadow-sm" x-data="detailReportForm()">
            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="h5 mb-1">
                            Tambah Temuan
                        </h2>

                        <div class="text-muted small">
                            Tambahkan temuan baru untuk KRT ini.
                        </div>
                    </div>

                    <button type="button" class="btn btn-sm btn-outline-primary" @click="showForm = !showForm">
                        + Tambah Temuan
                    </button>
                </div>

                <div x-show="showForm" x-cloak class="mt-3">

                    <form action="{{ route('reports.details.store', $report) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label for="keterangan_error" class="form-label fw-semibold">
                                Keterangan Temuan
                            </label>

                            <textarea id="keterangan_error" name="keterangan_error" class="form-control" rows="4"
                                placeholder="Jelaskan temuan atau kesalahan..." required>{{ old('keterangan_error') }}</textarea>

                            @error('keterangan_error')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- foto --}}
                        <div class="mb-3">
                            <label for="foto" class="form-label fw-semibold">
                                Foto Pendukung
                            </label>

                            <input type="file" id="foto" name="foto"
                                class="form-control @error('foto') is-invalid @enderror"
                                accept="image/jpeg,image/png,image/webp" @change="handlePhotoChange($event)">

                            <div class="form-text">
                                JPG, JPEG, PNG, atau WEBP. Maksimal 5 MB.
                            </div>

                            @error('foto')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div x-show="previewUrl" x-cloak class="mb-3">
                                <div class="text-muted small mb-2">
                                    Preview Foto
                                </div>

                                <div class="position-relative d-inline-block">
                                    <img :src="previewUrl" alt="Preview foto pendukung" class="img-fluid rounded border"
                                        style="max-height: 300px;">

                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-2"
                                        @click="removePhoto()" title="Hapus foto">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-light" @click="showForm = false; removePhoto()">
                                Batal
                            </button>

                            <button type="submit" class="btn btn-primary">
                                Simpan Temuan
                            </button>
                        </div>
                    </form>


                </div>

            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            function detailReportForm() {
                return {
                    showForm: false,
                    previewUrl: null,

                    handlePhotoChange(event) {
                        const file = event.target.files[0];

                        if (!file) {
                            this.removePhoto();
                            return;
                        }

                        const allowedTypes = [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ];

                        if (!allowedTypes.includes(file.type)) {
                            this.removePhoto();
                            alert('Format foto harus JPG, JPEG, PNG, atau WEBP.');
                            return;
                        }

                        if (file.size > 5 * 1024 * 1024) {
                            this.removePhoto();
                            alert('Ukuran foto maksimal 5 MB.');
                            return;
                        }

                        if (this.previewUrl) {
                            URL.revokeObjectURL(this.previewUrl);
                        }

                        this.previewUrl = URL.createObjectURL(file);
                    },

                    removePhoto() {
                        const input = document.getElementById('foto');

                        if (input) {
                            input.value = '';
                        }

                        if (this.previewUrl) {
                            URL.revokeObjectURL(this.previewUrl);
                        }

                        this.previewUrl = null;
                    }
                }
            }
        </script>
        <script>
            function editDetailReportForm() {
                return {
                    editing: false,
                    previewUrl: null,

                    handlePhotoChange(event) {
                        const file = event.target.files[0];

                        if (!file) {
                            this.removePhoto();
                            return;
                        }

                        const allowedTypes = [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ];

                        if (!allowedTypes.includes(file.type)) {
                            this.removePhoto();
                            alert('Format foto harus JPG, JPEG, atau WEBP.');
                            return;
                        }

                        if (file.size > 5 * 1024 * 1024) {
                            this.removePhoto();
                            alert('Ukuran foto maksimal 5 MB.');
                            return;
                        }

                        if (this.previewUrl) {
                            URL.revokeObjectURL(this.previewUrl);
                        }

                        this.previewUrl = URL.createObjectURL(file);
                    },

                    removePhoto() {
                        const input = this.$root.querySelector('input[type="file"]');

                        if (input) {
                            input.value = '';
                        }

                        if (this.previewUrl) {
                            URL.revokeObjectURL(this.previewUrl);
                        }

                        this.previewUrl = null;
                    },

                    cancelEdit() {
                        this.removePhoto();
                        this.editing = false;
                    }
                }
            }
        </script>
    @endpush
