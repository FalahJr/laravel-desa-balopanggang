<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\FieldDefinition;
use App\Models\FieldValue;
use App\Models\JenisSurat;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;


class SuratMasukController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = Surat::where('tipe_surat', 'masuk')->where('status', 'Pending')->latest()->get();

            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    if (Session('user')['role'] == 'admin') {

                        $prefix = 'admin'; // sesuaikan jika ada custom prefix
                        return '
                     <a class="btn btn-info btn-xs" href="' . url($prefix . '/surat-masuk/' . $item->id) . '">
            <i class="fas fa-eye"></i> &nbsp; Lihat
        </a>
                        <a class="btn btn-primary btn-xs" href="' . url($prefix . '/surat-masuk/' . $item->id . '/edit') . '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                        <form action="' . route('surat-masuk.destroy', $item->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Yakin ingin menghapus surat ini?\')">
                            ' . method_field('delete') . csrf_field() . '
                            <button class="btn btn-danger btn-xs" type="submit">
                                <i class="far fa-trash-alt"></i> &nbsp; Hapus
                            </button>
                        </form>
                    ';
                    } else if (Session('user')['role'] == 'kepala desa') {
                        $prefix = 'kepala-desa'; // sesuaikan jika ada custom prefix
                        return '
                     <a class="btn btn-info btn-xs" href="' . url($prefix . '/surat-masuk/' . $item->id) . '">
            <i class="fas fa-eye"></i> &nbsp; Lihat
        </a>
                       
                    ';
                    }
                })
                ->addIndexColumn()
                ->removeColumn('id')
                ->make();
        }

        return view('pages.admin.surat-masuk.index');
    }

    public function create(Request $request)
    {
        // Ambil semua jenis surat dengan tipe 'masuk' untuk dropdown
        $jenisSuratList = JenisSurat::where('tipe', 'masuk')->get();

        // Ambil jenis_surat_id dari query string jika sudah dipilih
        $selectedJenisSuratId = $request->query('jenis_surat_id');

        // Default: tidak ada field dinamis
        $fieldDefinitions = collect();

        // Kalau user sudah pilih jenis surat, ambil field-field dinamis
        if ($selectedJenisSuratId) {
            $selectedJenisSurat = JenisSurat::findOrFail($selectedJenisSuratId);
            $fieldDefinitions = $selectedJenisSurat->field_definitions()->where('is_active', 'Y')->get();
        } else {
            $selectedJenisSurat = null;
        }

        // dd($fieldDefinitions);

        return view('pages.admin.surat-masuk.create', [
            'jenisSuratList' => $jenisSuratList,
            'selectedJenisSuratId' => $selectedJenisSuratId,
            'selectedJenisSurat' => $selectedJenisSurat,
            'fieldDefinitions' => $fieldDefinitions
        ]);
    }


    public function store(Request $request)
    {
        // Validasi statis dulu
        $request->validate([
            'tgl_surat'      => 'required|date',
            'nama_surat'     => 'required|string|max:255',
            'file_lampiran'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'jenis_surat_id' => 'required|exists:jenis_surat,id',
        ]);

        // Ambil field definitions sesuai jenis surat yang dipilih
        $fieldDefinitions = FieldDefinition::where('jenis_surat_id', $request->jenis_surat_id)
            ->where('is_active', 'Y')
            ->get();

        // Siapkan aturan validasi dinamis
        $dynamicRules = [];
        foreach ($fieldDefinitions as $field) {
            $rule = [];
            if ($field->is_required === 'Y') {
                $rule[] = 'required';
            } else {
                $rule[] = 'nullable';
            }

            // Validasi tipe data sesuai tipe_input
            switch ($field->tipe_input) {
                case 'number':
                    $rule[] = 'numeric';
                    break;
                case 'email':
                    $rule[] = 'email';
                    break;
                case 'date':
                    $rule[] = 'date';
                    break;
                    // text & textarea tidak perlu aturan khusus
            }

            $dynamicRules['field_values.' . $field->id] = implode('|', $rule);
        }

        // Validasi dinamis untuk field_values
        $request->validate($dynamicRules);

        DB::beginTransaction();

        try {
            $filePath = null;

            // Generate nomor_surat otomatis dengan 2 digit untuk nomor urut
            $lastSurat = Surat::orderBy('id', 'desc')->first();
            $nextId = $lastSurat ? $lastSurat->id + 1 : 1;
            $nextIdFormatted = str_pad($nextId, 2, '0', STR_PAD_LEFT);

            $tanggalSuratFormatted = \Carbon\Carbon::parse($request->tgl_surat)->format('dmY');

            $nomorSurat = $request->jenis_surat_id . '/' . $nextIdFormatted . '/' . $tanggalSuratFormatted;

            // Cek apakah ada file lampiran baru yang diupload
            if ($request->hasFile('file_lampiran')) {
                $filePath = $request->file('file_lampiran')->store('assets/lampiran', 'public');
            }

            // Simpan surat masuk
            $surat = Surat::create([
                'nomor_surat'       => $nomorSurat,
                'tanggal_surat'     => $request->tgl_surat,
                'nama_surat'        => $request->nama_surat,
                'file_lampiran'     => $filePath, // bisa null jika tidak ada upload
                'tipe_surat'        => 'masuk',
                'jenis_surat_id'    => $request->jenis_surat_id,
                'created_by'        => 'admin', // sesuaikan dengan auth user
                'status'            => 'Pending',
            ]);

            // Simpan field dinamis jika ada input
            if ($request->filled('field_values')) {
                foreach ($request->input('field_values') as $fieldId => $value) {
                    FieldValue::create([
                        'surat_id'             => $surat->id,
                        'field_definition_id'  => $fieldId,
                        'value'                => $value,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }




    public function edit($id)
    {
        $surat = Surat::with('fieldValues')->findOrFail($id);

        $jenisSuratList = JenisSurat::where('tipe', 'masuk')->get();


        // Ambil field definition berdasarkan jenis_surat_id dari surat
        $fieldDefinitions = FieldDefinition::where('jenis_surat_id', $surat->jenis_surat_id)
            ->where('is_active', 'Y')
            ->get();

        // Kelompokkan nilai field berdasarkan id field definition
        $fieldValues = $surat->fieldValues->keyBy('field_definition_id');

        return view('pages.admin.surat-masuk.edit', compact('surat', 'fieldDefinitions', 'fieldValues', 'jenisSuratList'));
    }


    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        // Validasi field statis sesuai form input
        $request->validate([
            'nomor_surat'       => 'required|string|max:100',
            'tanggal_surat'      => 'required|date',
            'nama_surat'     => 'required|string|max:255',
            'jenis_surat_id' => 'required|exists:jenis_surat,id',
            'file_lampiran'  => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        // Ambil field definitions berdasar jenis_surat_id baru
        $fieldDefinitions = FieldDefinition::where('jenis_surat_id', $request->jenis_surat_id)->get();

        // Validasi dinamis
        $rules = [];
        foreach ($fieldDefinitions as $field) {
            $rule = $field->is_required === 'Y' ? 'required' : 'nullable';

            switch ($field->tipe_input) {
                case 'number':
                    $rule .= '|numeric';
                    break;
                case 'email':
                    $rule .= '|email';
                    break;
                case 'date':
                    $rule .= '|date';
                    break;
            }

            $rules['field_values.' . $field->id] = $rule;
        }

        $request->validate($rules);

        DB::beginTransaction();

        try {
            // Update surat statis
            $surat->nomor_surat = $request->nomor_surat;
            $surat->tanggal_surat = $request->tanggal_surat;
            $surat->nama_surat = $request->nama_surat;
            $surat->jenis_surat_id = $request->jenis_surat_id;

            // Update file lampiran jika ada file baru
            if ($request->hasFile('file_lampiran')) {
                if ($surat->file_lampiran && \Storage::disk('public')->exists($surat->file_lampiran)) {
                    \Storage::disk('public')->delete($surat->file_lampiran);
                }
                $filePath = $request->file('file_lampiran')->store('assets/lampiran', 'public');
                $surat->file_lampiran = $filePath;
            }

            $surat->save();

            $inputFieldValues = $request->input('field_values', []);

            foreach ($fieldDefinitions as $field) {
                // Cegah value null, ganti dengan string kosong
                $value = $inputFieldValues[$field->id] ?? '';
                if ($value === null) {
                    $value = '';
                }

                FieldValue::updateOrCreate(
                    ['surat_id' => $surat->id, 'field_definition_id' => $field->id],
                    ['value' => $value]
                );
            }

            DB::commit();

            return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal memperbarui data: ' . $e->getMessage())->withInput();
        }
    }


    public function destroy($id)
    {
        $surat = Surat::findOrFail($id);

        DB::beginTransaction();

        try {
            FieldValue::where('surat_id', $surat->id)->delete();
            $surat->delete();

            DB::commit();

            return redirect()->route('surat-masuk.index')->with('success', 'Surat masuk berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Ambil surat beserta relasi jenis_surat dan fieldValues
        $surat = Surat::with(['jenisSurat', 'fieldValues'])->findOrFail($id);

        // Ambil field definitions dari jenis surat ini (aktif saja)
        $fieldDefinitions = FieldDefinition::where('jenis_surat_id', $surat->jenis_surat_id)
            ->where('is_active', 'Y')
            ->get();

        // Mapping value berdasarkan field_definition_id
        $fieldValues = $surat->fieldValues->keyBy('field_definition_id');

        return view('pages.admin.surat-masuk.show', compact('surat', 'fieldDefinitions', 'fieldValues'));
    }

    public function download($id)
    {
        $surat = Surat::with(['jenisSurat', 'fieldValues.fieldDefinition'])->findOrFail($id);

        // Mapping field dinamis dengan label-nya
        $fields = $surat->fieldValues->map(function ($fv) {
            return [
                'label' => $fv->fieldDefinition->label,
                'value' => $fv->value,
            ];
        });

        // URL file lampiran jika ada
        $lampiranUrl = $surat->file_lampiran
            ? url('public/storage/' . $surat->file_lampiran)
            : null;

        return view('pages.admin.surat-masuk.download', compact('surat', 'fields', 'lampiranUrl'));
    }

    public function approve(Request $request, $id)
    {
        // dd('Function approve terpanggil');

        $item = Surat::findOrFail($id);

        if ($item->status === 'Diterima') {
            return redirect()->back()->with('info', 'Surat ini sudah disetujui sebelumnya.');
        }

        $item->update(['status' => 'Diterima']);

        return redirect()->back()
            ->with('success', 'Surat berhasil disetujui.');
    }

    public function reject(Request $request, $id)
    {
        $item = Surat::findOrFail($id);

        if ($item->status === 'Ditolak') {
            return redirect()->back()->with('info', 'Surat ini sudah ditolak sebelumnya.');
        }

        $item->update(['status' => 'Ditolak']);

        return redirect()->back()
            ->with('success', 'Surat berhasil ditolak.');
    }
}

// SuratKeluarController identik, hanya ganti semua 'masuk' menjadi 'keluar' dan view-nya ke 'surat-keluar'
