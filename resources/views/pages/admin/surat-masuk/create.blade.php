@extends('layouts.admin')

@section('title', 'Tambah Surat Masuk')

@section('container')
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-xl px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3">
                        <div class="col-auto mb-3">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="file-text"></i></div>
                                Tambah Surat Masuk
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mb-3">
                            <a class="btn btn-sm btn-light text-primary" href="{{ route('surat-masuk.index') }}">
                                <i class="me-1" data-feather="arrow-left"></i>
                                Kembali ke Semua Surat Masuk
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-4">
            <div class="row">
                <div class="col-xl-8">
                    <div class="card mb-4">
                        <div class="card-header">Form Tambah Surat Masuk</div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form
                                action="{{ Session('user')['role'] == 'admin' ? route('surat-masuk.store') : route('surat-masuk.storeStaff') }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf

                                {{-- Nomor Surat DIHAPUS --}}

                                <div class="mb-3">
                                    <label for="tgl_surat" class="small mb-1">Tanggal Surat</label>
                                    <input type="date" name="tgl_surat" id="tgl_surat"
                                        class="form-control @error('tgl_surat') is-invalid @enderror"
                                        value="{{ old('tgl_surat') }}" required>
                                    @error('tgl_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nama_surat" class="small mb-1">Nama Surat</label>
                                    <input type="text" name="nama_surat" id="nama_surat"
                                        class="form-control @error('nama_surat') is-invalid @enderror"
                                        value="{{ old('nama_surat') }}" required>
                                    @error('nama_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="file_lampiran" class="small mb-1">File Lampiran</label>
                                    <input type="file" name="file_lampiran" id="file_lampiran" required
                                        class="form-control @error('file_lampiran') is-invalid @enderror">
                                    @error('file_lampiran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="jenis_surat_id" class="small mb-1">Jenis Surat</label>
                                    <select name="jenis_surat_id" id="jenis_surat_id"
                                        class="form-select @error('jenis_surat_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Jenis Surat --</option>
                                        @foreach ($jenisSuratList as $jenis)
                                            <option value="{{ $jenis->id }}"
                                                {{ old('jenis_surat_id') == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis_surat_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="dynamic-fields"></div>

                                <button type="submit" class="btn btn-primary mt-3">Tambah Surat Masuk</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const jenisSuratSelect = document.getElementById("jenis_surat_id");
            const dynamicFieldsContainer = document.getElementById("dynamic-fields");

            function loadDynamicFields(jenisSuratId) {
                dynamicFieldsContainer.innerHTML = '<div class="text-muted">Memuat field tambahan...</div>';

                fetch(`{{ url('/field-definitions') }}/${jenisSuratId}`)
                    .then(response => response.json())
                    .then(data => {
                        dynamicFieldsContainer.innerHTML = ''; // clear loader
                        if (data.length === 0) {
                            dynamicFieldsContainer.innerHTML =
                                '<div class="text-muted">Tidak ada field tambahan.</div>';
                            return;
                        } else {
                            dynamicFieldsContainer.innerHTML =
                                '<div class="mb-2 mt-2 text-danger">Dokumen tambahan : </div>';
                        }

                        data.forEach(field => {
                            const isRequired = field.is_required === 'Y' ? 'required' : '';
                            const requiredAsterisk = field.is_required === 'Y' ?
                                ' <span class="text-danger">*</span>' : '';

                            let inputHtml = '';

                            switch (field.tipe_input) {
                                case 'text':
                                case 'email':
                                case 'number':
                                case 'date':
                                    inputHtml =
                                        `<input type="${field.tipe_input}" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${isRequired}>`;
                                    break;
                                case 'textarea':
                                    inputHtml =
                                        `<textarea name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${isRequired}></textarea>`;
                                    break;
                                default:
                                    // fallback ke text jika tipe tidak dikenali
                                    inputHtml =
                                        `<input type="text" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${isRequired}>`;
                                    break;
                            }

                            const html = `<div class="mb-3">
                            <label class="small mb-1" for="field_${field.id}">${field.label}${requiredAsterisk}</label>
                            ${inputHtml}
                        </div>`;

                            dynamicFieldsContainer.insertAdjacentHTML('beforeend', html);
                        });
                    })
                    .catch(error => {
                        console.error("Error fetching dynamic fields:", error);
                        dynamicFieldsContainer.innerHTML =
                            '<div class="text-danger">Gagal memuat field tambahan.</div>';
                    });
            }

            jenisSuratSelect.addEventListener("change", function() {
                const selectedId = this.value;
                if (selectedId) {
                    loadDynamicFields(selectedId);
                } else {
                    dynamicFieldsContainer.innerHTML = '';
                }
            });

            // Auto-load jika ada old value (misalnya dari validasi error)
            const preselectedId = jenisSuratSelect.value;
            if (preselectedId) {
                loadDynamicFields(preselectedId);
            }
        });
    </script>

@endsection
