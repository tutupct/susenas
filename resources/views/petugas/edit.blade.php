@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">


            <div class="d-flex align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit Petugas</h1>
                    <p class="text-muted mb-0">
                        Perbarui data petugas lapangan.
                    </p>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <form action="{{ route('petugas.update', $petugas) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Nama --}}
                        <div class="mb-3">
                            <label for="nama" class="form-label fw-semibold">
                                Nama Petugas
                            </label>

                            <input type="text" id="nama" name="nama" value="{{ old('nama', $petugas->nama) }}"
                                class="form-control @error('nama') is-invalid @enderror" placeholder="Masukkan nama petugas"
                                required autofocus>

                            @error('nama')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- No WhatsApp --}}
                        <div class="mb-3">
                            <label for="no_wa" class="form-label fw-semibold">
                                Nomor WhatsApp
                            </label>

                            <input type="text" id="no_wa" name="no_wa" value="{{ old('no_wa', $petugas->no_wa) }}"
                                class="form-control @error('no_wa') is-invalid @enderror" placeholder="Contoh: 081234567890"
                                inputmode="numeric" required>

                            <div class="form-text">
                                Nomor akan disimpan dalam format WhatsApp Indonesia (628...).
                            </div>

                            @error('no_wa')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- SLS --}}
                        <div class="mb-4">
                            <label for="sls" class="form-label fw-semibold">
                                SLS
                            </label>

                            <input type="text" id="sls" name="sls" value="{{ old('sls', $petugas->sls) }}"
                                class="form-control @error('sls') is-invalid @enderror" placeholder="Masukkan SLS" required>

                            @error('sls')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Tombol --}}
                        <div class="d-flex justify-content-end gap-2">

                            <a href="{{ route('petugas.index') }}" class="btn btn-light">
                                Batal
                            </a>

                            <button type="submit" class="btn btn-primary">
                                Simpan Perubahan
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>


    </div>
@endsection
