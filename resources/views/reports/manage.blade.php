@extends('layouts.app')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-9 col-xl-8">


            <div class="mb-4">
                <h1 class="h3 mb-1">Kelola KRT</h1>
                <p class="text-muted mb-0">
                    Pilih petugas dan masukkan daftar KRT yang akan divalidasi.
                </p>
            </div>

            <div class="card border-0 shadow-sm" x-data="reportForm()">
                <div class="card-body p-4">

                    <form action="{{ route('reports.store') }}" method="POST">
                        @csrf

                        {{-- Petugas --}}
                        <div class="mb-4">
                            <label for="petugas_id" class="form-label fw-semibold">
                                Petugas
                            </label>

                            <select id="petugas_id" name="petugas_id"
                                class="form-select @error('petugas_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Petugas --</option>

                                @foreach ($petugas as $item)
                                    <option value="{{ $item->id }}" @selected(old('petugas_id') == $item->id)>
                                        {{ $item->nama }} — {{ $item->sls }}
                                    </option>
                                @endforeach
                            </select>

                            @error('petugas_id')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Daftar KRT --}}
                        <div class="mb-3">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <label class="form-label fw-semibold mb-0">
                                        Daftar KRT
                                    </label>
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-primary" @click="addRow()">
                                    + Tambah KRT
                                </button>
                            </div>

                            <div class="table-responsive border rounded">

                                <table class="table table-hover align-middle mb-0">

                                    <thead class="table-light">
                                        <tr>
                                            <th width="100">Urutan</th>
                                            <th>Nama KRT</th>
                                            <th width="60"></th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        <template x-for="(row, index) in rows" :key="row.key">
                                            <tr>

                                                <td class="text-center">
                                                    <span class="fw-semibold" x-text="index + 1"></span>

                                                    <input type="hidden" :name="`reports[${index}][urutan]`"
                                                        :value="index + 1">
                                                </td>

                                                <td>
                                                    <input type="text" class="form-control"
                                                        :name="`reports[${index}][nama_krt]`" x-model="row.nama_krt"
                                                        placeholder="Masukkan nama KRT" required>
                                                </td>

                                                <td class="text-center">

                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                        @click="removeRow(index)" x-show="rows.length > 1" title="Hapus">
                                                        &times;
                                                    </button>

                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-primary" @click="addRow()">
                                    + Tambah KRT
                                </button>
                            </div>

                            <div class="form-text mt-2">
                                Urutan akan dibuat otomatis berdasarkan posisi baris.
                            </div>

                            @error('reports')
                                <div class="text-danger small mt-2">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Tombol --}}
                        <div class="d-flex justify-content-end gap-2 mt-4">

                            <a href="{{ route('reports.index') }}" class="btn btn-light">
                                Batal
                            </a>

                            <button type="submit" class="btn btn-primary" :disabled="rows.length === 0">
                                Simpan
                            </button>

                        </div>

                    </form>

                </div>
            </div>

        </div>


    </div>
@endsection

@push('scripts')
    <script>
        function reportForm() {
            return {
                rows: [{
                    key: Date.now(),
                    nama_krt: ''
                }],

                addRow() {
                    this.rows.push({
                        key: Date.now() + Math.random(),
                        nama_krt: ''
                    });
                    this.$nextTick(() => {
                        const inputs = this.$root.querySelectorAll('input[type="text"]');
                        inputs[inputs.length - 1]?.focus();
                    });
                },

                removeRow(index) {
                    if (this.rows.length <= 1) {
                        return;
                    }

                    this.rows.splice(index, 1);
                }
            }
        }
    </script>
@endpush
