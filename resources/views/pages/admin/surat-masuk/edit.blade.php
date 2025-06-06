@extends('layouts.admin')

@section('title', 'Ubah Surat Masuk')

@section('container')
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-xl px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3">
                        <div class="col-auto mb-3">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="file-text"></i></div>
                                Ubah Surat Masuk
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
                        <div class="card-header">Form Ubah Surat Masuk</div>
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form
                                action="{{ Session('user')['role'] == 'admin' ? route('surat-masuk.update', $surat->id) : route('surat-masuk.updateStaff', $surat->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="nomor_surat" class="small mb-1">Nomor Surat</label>
                                    <input type="text" name="nomor_surat" id="nomor_surat"
                                        class="form-control @error('nomor_surat') is-invalid @enderror"
                                        value="{{ old('nomor_surat', $surat->nomor_surat) }}" required>
                                    @error('nomor_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="tanggal_surat" class="small mb-1">Tanggal Surat</label>
                                    <input type="date" name="tanggal_surat" id="tanggal_surat"
                                        class="form-control @error('tanggal_surat') is-invalid @enderror"
                                        value="{{ old('tanggal_surat', $surat->tanggal_surat ? $surat->tanggal_surat->format('Y-m-d') : '') }}"
                                        required>
                                    @error('tanggal_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nama_surat" class="small mb-1">Nama Surat</label>
                                    <input type="text" name="nama_surat" id="nama_surat"
                                        class="form-control @error('nama_surat') is-invalid @enderror"
                                        value="{{ old('nama_surat', $surat->nama_surat) }}" required>
                                    @error('nama_surat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="file_lampiran" class="small mb-1">File Lampiran (kosongkan jika tidak
                                        diubah)</label>
                                    <input type="file" name="file_lampiran" id="file_lampiran"
                                        class="form-control @error('file_lampiran') is-invalid @enderror">
                                    @error('file_lampiran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($surat->file_lampiran)
                                        <small class="text-muted">File saat ini:
                                            <a href="{{ asset('/public/' . $surat->file_lampiran) }}" target="_blank">Lihat
                                                Lampiran</a>
                                        </small>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label for="jenis_surat_id" class="small mb-1">Jenis Surat</label>
                                    <select name="jenis_surat_id" id="jenis_surat_id"
                                        class="form-select @error('jenis_surat_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Jenis Surat --</option>
                                        @foreach ($jenisSuratList as $jenis)
                                            <option value="{{ $jenis->id }}"
                                                {{ old('jenis_surat_id', $surat->jenis_surat_id) == $jenis->id ? 'selected' : '' }}>
                                                {{ $jenis->nama }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jenis_surat_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Field Dinamis --}}
                                <div id="dynamic-fields">
                                    @foreach ($fieldDefinitions as $field)
                                        <div class="mb-3">
                                            <label class="small mb-1" for="field_{{ $field->id }}">
                                                {{ $field->label }}{{ $field->is_required === 'Y' ? ' *' : '' }}
                                            </label>

                                            @php
                                                $value = old(
                                                    "field_values.{$field->id}",
                                                    $fieldValues[$field->id]->value ?? '',
                                                );
                                            @endphp

                                            @if ($field->tipe_input === 'text')
                                                <input type="text" name="field_values[{{ $field->id }}]"
                                                    id="field_{{ $field->id }}"
                                                    class="form-control @error("field_values.{$field->id}") is-invalid @enderror"
                                                    value="{{ $value }}"
                                                    {{ $field->is_required === 'Y' ? 'required' : '' }}>
                                            @elseif ($field->tipe_input === 'textarea')
                                                <textarea name="field_values[{{ $field->id }}]" id="field_{{ $field->id }}"
                                                    class="form-control @error("field_values.{$field->id}") is-invalid @enderror"
                                                    {{ $field->is_required === 'Y' ? 'required' : '' }}>{{ $value }}</textarea>
                                            @elseif ($field->tipe_input === 'date')
                                                <input type="date" name="field_values[{{ $field->id }}]"
                                                    id="field_{{ $field->id }}"
                                                    class="form-control @error("field_values.{$field->id}") is-invalid @enderror"
                                                    value="{{ $value }}"
                                                    {{ $field->is_required === 'Y' ? 'required' : '' }}>
                                            @elseif ($field->tipe_input === 'number')
                                                <input type="number" name="field_values[{{ $field->id }}]"
                                                    id="field_{{ $field->id }}"
                                                    class="form-control @error("field_values.{$field->id}") is-invalid @enderror"
                                                    value="{{ $value }}"
                                                    {{ $field->is_required === 'Y' ? 'required' : '' }}>
                                            @elseif ($field->tipe_input === 'email')
                                                <input type="email" name="field_values[{{ $field->id }}]"
                                                    id="field_{{ $field->id }}"
                                                    class="form-control @error("field_values.{$field->id}") is-invalid @enderror"
                                                    value="{{ $value }}"
                                                    {{ $field->is_required === 'Y' ? 'required' : '' }}>
                                            @endif

                                            @error("field_values.{$field->id}")
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>

                                <button type="submit" class="btn btn-primary">Perbarui Surat Masuk</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('jenis_surat_id').addEventListener('change', function() {
            const jenisSuratId = this.value;
            const container = document.getElementById('dynamic-fields');

            if (!jenisSuratId) {
                container.innerHTML = '';
                return;
            }

            fetch(`{{ url('/field-definitions') }}/${jenisSuratId}`)
                .then(response => response.json())
                .then(fields => {
                    let html = '';

                    fields.forEach(field => {
                        const required = field.is_required === 'Y' ? 'required' : '';
                        const label = field.label + (field.is_required === 'Y' ? ' *' : '');
                        let inputHtml = '';

                        switch (field.tipe_input) {
                            case 'text':
                                inputHtml =
                                    `<input type="text" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${required}>`;
                                break;
                            case 'textarea':
                                inputHtml =
                                    `<textarea name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${required}></textarea>`;
                                break;
                            case 'date':
                                inputHtml =
                                    `<input type="date" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${required}>`;
                                break;
                            case 'number':
                                inputHtml =
                                    `<input type="number" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${required}>`;
                                break;
                            case 'email':
                                inputHtml =
                                    `<input type="email" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${required}>`;
                                break;
                            default:
                                inputHtml =
                                    `<input type="text" name="field_values[${field.id}]" id="field_${field.id}" class="form-control" ${required}>`;
                        }

                        html += `
                    <div class="mb-3">
                        <label class="small mb-1" for="field_${field.id}">${label}</label>
                        ${inputHtml}
                    </div>
                `;
                    });

                    container.innerHTML = html;
                })
                .catch(() => {
                    container.innerHTML = '<div class="alert alert-danger">Gagal memuat field dinamis.</div>';
                });
        });
    </script>
@endsection
