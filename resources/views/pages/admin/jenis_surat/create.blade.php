@extends('layouts.admin')

@section('title')
    Tambah Jenis Surat
@endsection

@section('container')
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-xl px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3">
                        <div class="col-auto mb-3">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="file-text"></i></div>
                                Tambah Jenis Surat
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mb-3">
                            <a class="btn btn-sm btn-light text-primary" href="{{ route('jenis-surat.index') }}">
                                <i class="me-1" data-feather="arrow-left"></i>
                                Kembali ke Semua Jenis Surat
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
                        <div class="card-header">Form Tambah Jenis Surat</div>
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
                            <form action="{{ route('jenis-surat.store') }}" method="POST" id="formJenisSurat">
                                @csrf
                                <div class="mb-3">
                                    <label for="nama" class="small mb-1">Nama Jenis Surat</label>
                                    <input type="text" name="nama" id="nama"
                                        class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}"
                                        required autofocus>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="addFieldsToggle"
                                        name="add_fields" {{ old('add_fields') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="addFieldsToggle">Tambah Field Dinamis untuk Jenis
                                        Surat ini</label>
                                </div>

                                <div id="fieldsContainer" style="display: {{ old('add_fields') ? 'block' : 'none' }};">
                                    <label class="small mb-2">Field Definitions</label>
                                    <div id="fieldDefinitionsWrapper">
                                        @if (old('fields'))
                                            @foreach (old('fields') as $index => $field)
                                                <div class="field-definition mb-3 border rounded p-3 position-relative">
                                                    <button type="button"
                                                        class="btn-close position-absolute top-0 end-0 remove-field-btn"
                                                        aria-label="Remove"></button>
                                                    <div class="mb-2">
                                                        <label class="form-label">Label Field</label>
                                                        <input type="text" name="fields[{{ $index }}][label]"
                                                            class="form-control" value="{{ $field['label'] ?? '' }}"
                                                            required>
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label">Tipe Field</label>
                                                        <select name="fields[{{ $index }}][type]" class="form-select"
                                                            required>
                                                            <option value="text"
                                                                {{ isset($field['type']) && $field['type'] == 'text' ? 'selected' : '' }}>
                                                                Text</option>
                                                            <option value="number"
                                                                {{ isset($field['type']) && $field['type'] == 'number' ? 'selected' : '' }}>
                                                                Number</option>
                                                            <option value="date"
                                                                {{ isset($field['type']) && $field['type'] == 'date' ? 'selected' : '' }}>
                                                                Date</option>
                                                            <option value="email"
                                                                {{ isset($field['type']) && $field['type'] == 'email' ? 'selected' : '' }}>
                                                                Email</option>
                                                            <option value="textarea"
                                                                {{ isset($field['type']) && $field['type'] == 'textarea' ? 'selected' : '' }}>
                                                                Textarea</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-2 form-check">
                                                        <input type="hidden" name="fields[{{ $index }}][required]"
                                                            value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="fields[{{ $index }}][required]"
                                                            id="required_{{ $index }}" value="1"
                                                            {{ !empty($field['required']) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="required_{{ $index }}">Wajib Diisi</label>
                                                    </div>
                                                    <div class="mb-2 form-check">
                                                        <input type="hidden" name="fields[{{ $index }}][is_active]"
                                                            value="N">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="fields[{{ $index }}][is_active]"
                                                            id="is_active_{{ $index }}" value="Y"
                                                            {{ !isset($field['is_active']) || $field['is_active'] === 'Y' ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="is_active_{{ $index }}">Aktifkan Kolom Ini</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="field-definition mb-3 border rounded p-3 position-relative">
                                                <button type="button"
                                                    class="btn-close position-absolute top-0 end-0 remove-field-btn"
                                                    aria-label="Remove"></button>
                                                <div class="mb-2">
                                                    <label class="form-label">Label Field</label>
                                                    <input type="text" name="fields[0][label]" class="form-control"
                                                        required>
                                                </div>
                                                <div class="mb-2">
                                                    <label class="form-label">Tipe Field</label>
                                                    <select name="fields[0][type]" class="form-select" required>
                                                        <option value="text">Text</option>
                                                        <option value="number">Number</option>
                                                        <option value="date">Date</option>
                                                        <option value="email">Email</option>
                                                        <option value="textarea">Textarea</option>
                                                    </select>
                                                </div>
                                                <div class="mb-2 form-check">
                                                    <input type="hidden" name="fields[0][required]" value="0">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[0][required]" id="required_0" value="1">
                                                    <label class="form-check-label" for="required_0">Wajib Diisi</label>
                                                </div>
                                                <div class="mb-2 form-check">
                                                    <input type="hidden" name="fields[0][is_active]" value="N">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[0][is_active]" id="is_active_0" value="Y"
                                                        checked>
                                                    <label class="form-check-label" for="is_active_0">Aktifkan Kolom
                                                        Ini</label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-secondary" id="addFieldBtn">
                                        <i data-feather="plus"></i> Tambah Field Baru
                                    </button>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">Tambah Jenis Surat</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://unpkg.com/feather-icons"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                feather.replace();
                const addFieldsToggle = document.getElementById('addFieldsToggle');
                const fieldsContainer = document.getElementById('fieldsContainer');
                const addFieldBtn = document.getElementById('addFieldBtn');
                const fieldDefinitionsWrapper = document.getElementById('fieldDefinitionsWrapper');

                function updateRemoveButtonsVisibility() {
                    const allRemoveBtns = fieldDefinitionsWrapper.querySelectorAll('.remove-field-btn');
                    allRemoveBtns.forEach(btn => btn.style.display = fieldDefinitionsWrapper.children.length <= 1 ?
                        'none' : 'block');
                }

                function toggleFieldsInputs(enabled) {
                    const inputs = fieldsContainer.querySelectorAll('input, select, button');
                    inputs.forEach(input => {
                        if (input !== addFieldBtn) input.disabled = !enabled;
                    });
                }

                addFieldsToggle.addEventListener('change', function() {
                    fieldsContainer.style.display = this.checked ? 'block' : 'none';
                    toggleFieldsInputs(this.checked);
                });

                toggleFieldsInputs(addFieldsToggle.checked);

                addFieldBtn.addEventListener('click', function() {
                    const index = fieldDefinitionsWrapper.children.length;
                    const fieldHtml = `
                        <div class="field-definition mb-3 border rounded p-3 position-relative">
                            <button type="button" class="btn-close position-absolute top-0 end-0 remove-field-btn" aria-label="Remove"></button>
                            <div class="mb-2">
                                <label class="form-label">Label Field</label>
                                <input type="text" name="fields[${index}][label]" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Tipe Field</label>
                                <select name="fields[${index}][type]" class="form-select" required>
                                    <option value="text">Text</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="email">Email</option>
                                    <option value="textarea">Textarea</option>
                                </select>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="hidden" name="fields[${index}][required]" value="0">
                                <input class="form-check-input" type="checkbox" name="fields[${index}][required]" id="required_${index}" value="1">
                                <label class="form-check-label" for="required_${index}">Wajib Diisi</label>
                            </div>
                            <div class="mb-2 form-check">
                                <input type="hidden" name="fields[${index}][is_active]" value="N">
                                <input class="form-check-input" type="checkbox" name="fields[${index}][is_active]" id="is_active_${index}" value="Y" checked>
                                <label class="form-check-label" for="is_active_${index}">Aktifkan Kolom Ini</label>
                            </div>
                        </div>`;

                    const temp = document.createElement('div');
                    temp.innerHTML = fieldHtml;
                    fieldDefinitionsWrapper.appendChild(temp.firstElementChild);
                    feather.replace();
                    bindRemoveButtons();
                    updateRemoveButtonsVisibility();
                });

                function bindRemoveButtons() {
                    document.querySelectorAll('.remove-field-btn').forEach(btn => {
                        btn.onclick = function() {
                            this.closest('.field-definition').remove();
                            [...fieldDefinitionsWrapper.children].forEach((fieldDiv, i) => {
                                fieldDiv.querySelectorAll('input, select').forEach(input => {
                                    const name = input.name;
                                    if (!name) return;
                                    input.name = name.replace(/fields\[\d+\]/,
                                        `fields[${i}]`);

                                    if (input.id) {
                                        const oldId = input.id;
                                        const newId = oldId.replace(/\d+/, i);
                                        input.id = newId;
                                        const label = fieldDiv.querySelector(
                                            `label[for='${oldId}']`);
                                        if (label) label.setAttribute('for', newId);
                                    }
                                });
                            });
                            updateRemoveButtonsVisibility();
                        }
                    });
                }

                bindRemoveButtons();
                updateRemoveButtonsVisibility();
            });
        </script>
    </main>
@endsection
