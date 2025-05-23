<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FieldDefinition;
use App\Models\JenisSurat;
use Yajra\DataTables\Facades\DataTables;

class JenisSuratController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = JenisSurat::get();

            return Datatables::of($query)
                ->addColumn('action', function ($item) {
                    $prefix = 'admin'; // sesuaikan prefix jika perlu, misal dari session user role
                    return '
                        <a class="btn btn-primary btn-xs" href="' . url($prefix . '/jenis-surat/' . $item->id . '/edit') . '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                        <form action="' . route('jenis-surat.destroy', $item->id) . '" method="POST" onsubmit="return confirm(\'Anda yakin ingin menghapus jenis surat ini?\')">
                            ' . method_field('delete') . csrf_field() . '
                            <button class="btn btn-danger btn-xs" type="submit">
                                <i class="far fa-trash-alt"></i> &nbsp; Hapus
                            </button>
                        </form>
                    ';
                })
                ->addIndexColumn()
                ->removeColumn('id')
                ->make();
        }

        return view('pages.admin.jenis_surat.index');
    }

    public function create()
    {
        return view('pages.admin.jenis_surat.create');
    }

    public function store(Request $request)
    {
        // Validasi dasar untuk jenis surat dan optional fields
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_surat,nama',
            'add_fields' => 'nullable|boolean',
            'fields' => 'nullable|array',
            'fields.*.label' => 'required_with:add_fields|distinct|string|max:255',
            'fields.*.type' => 'required_with:add_fields|in:text,number,date,email,textarea',
            'fields.*.required' => 'required|string',
            // 'fields.*.order' => 'required_with:add_fields|integer|min:1',
            'fields.*.is_active' => 'nullable|string|in:Y,N',
        ]);

        // dd($validatedData);
        // Buat data JenisSurat dulu
        $jenisSurat = JenisSurat::create([
            'nama' => $validatedData['nama'],
        ]);

        // Kalau checkbox tambah field dinamis dicentang dan ada fields
        if (!empty($validatedData['add_fields']) && !empty($validatedData['fields'])) {
            foreach ($validatedData['fields'] as $field) {
                FieldDefinition::create([
                    'jenis_surat_id' => $jenisSurat->id,
                    'nama_field' => \Str::slug($field['label'], '_'),
                    'label' => $field['label'],
                    'tipe_input' => $field['type'],
                    'is_required' => $field['required'] == "0" ? "N" : "Y",
                    // 'order' => $field['order'],
                    'is_active' => isset($field['is_active']) && $field['is_active'] === 'Y' ? 'Y' : 'N',
                ]);
            }
        }

        return redirect()
            ->route('jenis-surat.index')
            ->with('success', 'Sukses! Jenis Surat berhasil ditambahkan.');
    }




    public function edit($id)
    {
        $item = JenisSurat::with('fieldDefinitions')->findOrFail($id);

        return view('pages.admin.jenis_surat.edit', [
            'item' => $item
        ]);
    }


    public function update(Request $request, $id)
    {
        $item = JenisSurat::with('fieldDefinitions')->findOrFail($id);

        $validatedData = $request->validate([
            'nama' => 'required|string|max:255|unique:jenis_surat,nama,' . $item->id,
            // Jika ingin, bisa tambahkan validasi untuk fields di sini
        ]);

        // Update nama JenisSurat
        $item->update($validatedData);

        // Tangkap array field yang dihapus dari frontend (via modal hapus)
        $fieldsToDelete = $request->input('fields_to_delete', []);
        if (!empty($fieldsToDelete)) {
            foreach ($fieldsToDelete as $fieldId) {
                // Hapus FieldValue yang terkait
                \App\Models\FieldValue::where('field_definition_id', $fieldId)->delete();
                // Hapus FieldDefinition-nya
                \App\Models\FieldDefinition::where('id', $fieldId)->delete();
            }
        }

        if ($request->has('add_fields')) {
            $fields = $request->input('fields', []);

            $existingFieldIds = $item->fieldDefinitions->pluck('id')->toArray();
            $incomingIds = [];

            foreach ($fields as $fieldData) {
                $label = $fieldData['label'] ?? null;
                $type = $fieldData['type'] ?? 'text';
                $required = ($fieldData['required'] ?? 'N') === 'Y' ? 'Y' : 'N';
                $isActive = ($fieldData['active'] ?? 'N') === 'Y' ? 'Y' : 'N';

                if (!empty($fieldData['id'])) {
                    // Update field lama
                    $definition = $item->fieldDefinitions()->where('id', $fieldData['id'])->first();
                    if ($definition) {
                        $definition->update([
                            'label' => $label,
                            'tipe_input' => $type,
                            'is_required' => $required,
                            'is_active' => $isActive,
                        ]);
                        $incomingIds[] = $definition->id;
                    }
                } else {
                    // Insert field baru
                    $newDefinition = $item->fieldDefinitions()->create([
                        'nama_field' => \Str::slug($label, '_'),
                        'label' => $label,
                        'tipe_input' => $type,
                        'is_required' => $required,
                        'is_active' => $isActive,
                    ]);
                    $incomingIds[] = $newDefinition->id;
                }
            }

            // Hapus field lama yang tidak ada di input baru dan tidak punya nilai
            $fieldsToDelete2 = array_diff($existingFieldIds, $incomingIds);

            if (!empty($fieldsToDelete2)) {
                foreach ($fieldsToDelete2 as $fieldId) {
                    $hasValue = \App\Models\FieldValue::where('field_definition_id', $fieldId)->exists();

                    if (!$hasValue) {
                        \App\Models\FieldDefinition::where('id', $fieldId)->delete();
                    }
                }
            }
        } else {
            // Jika checkbox add_fields tidak dicentang,
            // hapus semua field yang tidak memiliki data isian
            foreach ($item->fieldDefinitions as $definition) {
                $hasValue = \App\Models\FieldValue::where('field_definition_id', $definition->id)->exists();
                if (!$hasValue) {
                    $definition->delete();
                }
            }
        }

        return redirect()
            ->route('jenis-surat.index')
            ->with('success', 'Sukses! Jenis Surat berhasil diperbarui.');
    }






    public function destroy($id)
    {
        $item = JenisSurat::with('fieldDefinitions')->findOrFail($id);

        // Hapus semua field definitions yang terkait
        $item->fieldDefinitions()->delete();

        // Hapus jenis surat
        $item->delete();

        return redirect()
            ->route('jenis-surat.index')
            ->with('success', 'Sukses! Jenis Surat berhasil dihapus.');
    }
}
