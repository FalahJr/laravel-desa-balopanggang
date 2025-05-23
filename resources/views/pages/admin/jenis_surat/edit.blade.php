@extends('layouts.admin')

@section('title')
    Ubah Jenis Surat
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
                                Ubah Jenis Surat
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
                        <div class="card-header">Form Ubah Jenis Surat</div>
                        <div class="card-body">

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button class="btn-close" type="button" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                            @endif

                            <form action="{{ route('jenis-surat.update', $item->id) }}" method="POST" id="formJenisSurat">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="nama" class="small mb-1">Nama Jenis Surat</label>
                                    <input type="text" name="nama" id="nama"
                                        class="form-control @error('nama') is-invalid @enderror"
                                        value="{{ old('nama', $item->nama) }}" required autofocus>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" value="1" id="addFieldsToggle"
                                        name="add_fields"
                                        {{ old('add_fields', $item->fieldDefinitions->count() > 0) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="addFieldsToggle">
                                        Ubah Field Dinamis untuk Jenis Surat ini
                                    </label>
                                </div>

                                <div id="fieldsContainer"
                                    style="display: {{ old('add_fields', $item->fieldDefinitions->count() > 0) ? 'block' : 'none' }};">
                                    <label class="small mb-2">Field Definitions</label>
                                    <div id="fieldDefinitionsWrapper">
                                        @php
                                            $fields = old('fields', $item->fieldDefinitions->toArray());
                                        @endphp
                                        @foreach ($fields as $index => $field)
                                            <div class="field-definition mb-3 border rounded p-3 position-relative"
                                                data-field-id="{{ $field['id'] ?? '' }}"
                                                data-has-value="{{ \App\Models\FieldValue::where('field_definition_id', $field['id'] ?? 0)->exists() ? '1' : '0' }}">
                                                <button type="button"
                                                    class="btn-close position-absolute top-0 end-0 remove-field-btn"
                                                    aria-label="Remove"></button>

                                                @if (isset($field['id']))
                                                    <input type="hidden" name="fields[{{ $index }}][id]"
                                                        value="{{ $field['id'] }}">
                                                @endif

                                                <div class="mb-2">
                                                    <label class="form-label">Label Field</label>
                                                    <input type="text" name="fields[{{ $index }}][label]"
                                                        class="form-control" value="{{ $field['label'] ?? '' }}" required>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label">Tipe Field</label>
                                                    <select name="fields[{{ $index }}][type]" class="form-select"
                                                        required>
                                                        @foreach (['text', 'number', 'date', 'email', 'textarea'] as $type)
                                                            <option value="{{ $type }}"
                                                                {{ ($field['tipe_input'] ?? $field['type']) == $type ? 'selected' : '' }}>
                                                                {{ ucfirst($type) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="mb-2 form-check">
                                                    <input type="hidden" name="fields[{{ $index }}][required]"
                                                        value="N">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[{{ $index }}][required]"
                                                        id="required_{{ $index }}" value="Y"
                                                        {{ ($field['is_required'] ?? ($field['required'] ?? 'N')) === 'Y' ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="required_{{ $index }}">Wajib Diisi</label>
                                                </div>

                                                <div class="mb-2 form-check">
                                                    <input type="hidden" name="fields[{{ $index }}][active]"
                                                        value="N">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[{{ $index }}][active]"
                                                        id="active_{{ $index }}" value="Y"
                                                        {{ ($field['is_active'] ?? ($field['active'] ?? 'N')) === 'Y' ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="active_{{ $index }}">Aktifkan Field Ini</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <button type="button" class="btn btn-secondary" id="addFieldBtn">
                                        <i data-feather="plus"></i> Tambah Field Baru
                                    </button>
                                </div>

                                {{-- Container untuk input hidden fields_to_delete --}}
                                <div id="fieldsToDeleteContainer"></div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    Perbarui Jenis Surat
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Konfirmasi Hapus Field dengan Data --}}
        <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Field</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        Field yang ingin Anda hapus memiliki data isian yang sudah tersimpan.<br>
                        Jika Anda melanjutkan, semua data nilai pada field ini akan ikut terhapus.<br><br>
                        Apakah Anda yakin ingin melanjutkan penghapusan?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Ya, Hapus</button>
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
                const formJenisSurat = document.getElementById('formJenisSurat');
                const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                const fieldsToDeleteContainer = document.getElementById('fieldsToDeleteContainer');

                let fieldsPendingRemoveWithData = [];

                function updateRemoveButtonsVisibility() {
                    const removeBtns = fieldDefinitionsWrapper.querySelectorAll('.remove-field-btn');
                    removeBtns.forEach(btn => btn.style.display = (fieldDefinitionsWrapper.children.length <= 1) ?
                        'none' : 'block');
                }

                function toggleFieldsInputs(enabled) {
                    const inputs = fieldsContainer.querySelectorAll('input, select, button');
                    inputs.forEach(input => {
                        if (input === addFieldBtn) return;
                        input.disabled = !enabled;
                    });
                }

                addFieldsToggle.addEventListener('change', function() {
                    if (this.checked) {
                        fieldsContainer.style.display = 'block';
                        toggleFieldsInputs(true);
                    } else {
                        fieldsContainer.style.display = 'none';
                        toggleFieldsInputs(false);
                    }
                });

                addFieldBtn.addEventListener('click', function() {
                    const index = fieldDefinitionsWrapper.children.length;
                    const fieldHtml = `
                <div class="field-definition mb-3 border rounded p-3 position-relative" data-has-value="0">
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
                        <input type="hidden" name="fields[${index}][required]" value="N">
                        <input class="form-check-input" type="checkbox" name="fields[${index}][required]" id="required_${index}" value="Y">
                        <label class="form-check-label" for="required_${index}">Wajib Diisi</label>
                    </div>
                    <div class="mb-2 form-check">
                        <input type="hidden" name="fields[${index}][active]" value="N">
                        <input class="form-check-input" type="checkbox" name="fields[${index}][active]" id="active_${index}" value="Y" checked>
                        <label class="form-check-label" for="active_${index}">Aktifkan Field Ini</label>
                    </div>
                </div>
                `;

                    const temp = document.createElement('div');
                    temp.innerHTML = fieldHtml.trim();
                    fieldDefinitionsWrapper.appendChild(temp.firstElementChild);

                    feather.replace();
                    bindRemoveButtons();
                    updateRemoveButtonsVisibility();
                });

                function bindRemoveButtons() {
                    const removeButtons = document.querySelectorAll('.remove-field-btn');
                    removeButtons.forEach(btn => {
                        btn.onclick = function() {
                            const fieldDiv = this.closest('.field-definition');
                            const hasValue = fieldDiv.getAttribute('data-has-value') === '1';
                            if (hasValue) {
                                fieldsPendingRemoveWithData.push(fieldDiv);
                                confirmDeleteModal.show();
                            } else {
                                fieldDiv.remove();
                                updateIndices();
                                updateRemoveButtonsVisibility();
                            }
                        };
                    });
                }

                function updateIndices() {
                    [...fieldDefinitionsWrapper.children].forEach((fieldDiv, i) => {
                        fieldDiv.querySelectorAll('input, select').forEach(input => {
                            const name = input.getAttribute('name');
                            if (name) {
                                const newName = name.replace(/fields\[\d+\]/, `fields[${i}]`);
                                input.setAttribute('name', newName);
                            }
                        });
                    });
                }

                confirmDeleteBtn.onclick = function() {
                    fieldsPendingRemoveWithData.forEach(fieldDiv => {
                        // Jika ada input hidden id, tambahkan id ke fields_to_delete
                        const inputId = fieldDiv.querySelector('input[name$="[id]"]');
                        if (inputId) {
                            const fieldId = inputId.value;
                            if (fieldId) {
                                const inputDelete = document.createElement('input');
                                inputDelete.type = 'hidden';
                                inputDelete.name = 'fields_to_delete[]';
                                inputDelete.value = fieldId;
                                fieldsToDeleteContainer.appendChild(inputDelete);
                            }
                        }
                        fieldDiv.remove();
                    });
                    fieldsPendingRemoveWithData = [];
                    confirmDeleteModal.hide();
                    updateIndices();
                    updateRemoveButtonsVisibility();
                };

                updateRemoveButtonsVisibility();
                bindRemoveButtons();
                toggleFieldsInputs(addFieldsToggle.checked);
            });
        </script>
    </main>
@endsection
