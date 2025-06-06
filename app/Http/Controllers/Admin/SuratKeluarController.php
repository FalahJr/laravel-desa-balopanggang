<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\FieldDefinition;
use App\Models\FieldValue;
use App\Models\JenisSurat;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;



class SuratKeluarController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $query = Surat::where('tipe_surat', 'keluar')->where('status', 'Pending')->latest()->get();

            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    if (Session('user')['role'] == 'admin') {

                        $prefix = 'admin'; // sesuaikan jika ada custom prefix
                        return '
                     <a class="btn btn-info btn-xs" href="' . url($prefix . '/surat-keluar/' . $item->id) . '">
            <i class="fas fa-eye"></i> &nbsp; Lihat
        </a>
                        <a class="btn btn-primary btn-xs" href="' . url($prefix . '/surat-keluar/' . $item->id . '/edit') . '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                        <form action="' . route('surat-keluar.destroy', $item->id) . '" method="POST" style="display:inline;" onsubmit="return confirm(\'Yakin ingin menghapus surat ini?\')">
                            ' . method_field('delete') . csrf_field() . '
                            <button class="btn btn-danger btn-xs" type="submit">
                                <i class="far fa-trash-alt"></i> &nbsp; Hapus
                            </button>
                        </form>
                    ';
                    } else if (Session('user')['role'] == 'kepala desa') {
                        $prefix = 'kepala-desa'; // sesuaikan jika ada custom prefix
                        return '
                     <a class="btn btn-info btn-xs" href="' . url($prefix . '/surat-keluar/' . $item->id) . '">
            <i class="fas fa-eye"></i> &nbsp; Lihat
        </a>
                       
                    ';
                    } else {

                        $prefix = 'staff'; // sesuaikan jika ada custom prefix
                        return '
                     <a class="btn btn-info btn-xs" href="' . url($prefix . '/surat-keluar/' . $item->id) . '">
            <i class="fas fa-eye"></i> &nbsp; Lihat
        </a>
                        <a class="btn btn-primary btn-xs" href="' . url($prefix . '/surat-keluar/' . $item->id . '/edit') . '">
                            <i class="fas fa-edit"></i> &nbsp; Ubah
                        </a>
                       
                    ';
                    }
                })
                ->addIndexColumn()
                ->removeColumn('id')
                ->make();
        }

        return view('pages.admin.surat-keluar.index');
    }

    public function create(Request $request)
    {
        // Ambil semua jenis surat dengan tipe 'masuk' untuk dropdown
        $jenisSuratList = JenisSurat::where('tipe', 'keluar')->get();

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

        return view('pages.admin.surat-keluar.create', [
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
            'nomor_surat'    => 'required|string|max:100',
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
                case 'file':
                    $rule[] = 'file';
                    $rule[] = 'mimes:pdf,jpg,jpeg,png,doc,docx';
                    $rule[] = 'max:2048';
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
            // if ($request->hasFile('file_lampiran')) {
            //     $filePath = $request->file('file_lampiran')->store('assets/lampiran', 'public');
            // }

            // Upload file ke public/assets/lampiran
            if ($request->hasFile('file_lampiran')) {
                $file = $request->file('file_lampiran');
                $filename = time() . '_' . $file->getClientOriginalName();
                $destinationPath = public_path('assets/lampiran');
                $file->move($destinationPath, $filename);
                $filePath = 'assets/lampiran/' . $filename; // Simpan path relatif ke file
            }


            // Simpan surat masuk
            $surat = Surat::create([
                // 'nomor_surat'       => $nomorSurat,
                'nomor_surat'       => $request->nomor_surat,
                'tanggal_surat'     => $request->tgl_surat,
                'nama_surat'        => $request->nama_surat,
                'file_lampiran'     => $filePath, // bisa null jika tidak ada upload
                'tipe_surat'        => 'keluar',
                'jenis_surat_id'    => $request->jenis_surat_id,
                'created_by'        => 'admin', // sesuaikan dengan auth user
                'status'            => 'Pending',
            ]);

            // Simpan field dinamis jika ada input
            if ($request->filled('field_values')) {
                if ($request->filled('field_values')) {
                    foreach ($fieldDefinitions as $field) {
                        $fieldId = $field->id;
                        $fieldKey = "field_values.$fieldId";

                        if ($field->tipe_input === 'file' && $request->hasFile("field_values.$fieldId")) {
                            $uploadedFile = $request->file("field_values.$fieldId");

                            $folderPath = public_path("assets/lampiran/surat-keluar/{$surat->id}");
                            if (!file_exists($folderPath)) {
                                mkdir($folderPath, 0777, true);
                            }

                            $filename = time() . '_' . $uploadedFile->getClientOriginalName();
                            $uploadedFile->move($folderPath, $filename);

                            $value = "assets/lampiran/surat-keluar/{$surat->id}/" . $filename;
                        } else {
                            // selain file, ambil value biasa
                            $value = $request->input("field_values.$fieldId");
                        }

                        FieldValue::create([
                            'surat_id'            => $surat->id,
                            'field_definition_id' => $fieldId,
                            'value'               => $value,
                        ]);
                    }
                }
            }

            // Tambahkan notifikasi untuk Kepala Desa
            $notifikasi = new Notifikasi;
            $notifikasi->role = 'kepala desa';
            $notifikasi->judul = "Terdapat surat keluar baru # '" . $nomorSurat;
            $notifikasi->deskripsi = "Terdapat surat keluar baru dengan nomor '" . $nomorSurat . "' yang perlu segera diverifikasi.";
            $notifikasi->is_seen = 'N';
            $notifikasi->created_at = \Carbon\Carbon::now();
            $notifikasi->updated_at = \Carbon\Carbon::now();
            $notifikasi->save();
            DB::commit();

            // return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil disimpan.');
            if (Session('user')['role'] == 'admin') {
                return redirect('/admin/surat-keluar')->with('success', 'Surat keluar berhasil disimpan.');
            } else {
                return redirect('/staff/surat-keluar')->with('success', 'Surat keluar berhasil disimpan.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Gagal menyimpan data: ' . $e->getMessage())->withInput();
        }
    }




    public function edit($id)
    {
        $surat = Surat::with('fieldValues')->findOrFail($id);

        $jenisSuratList = JenisSurat::where('tipe', 'keluar')->get();


        // Ambil field definition berdasarkan jenis_surat_id dari surat
        $fieldDefinitions = FieldDefinition::where('jenis_surat_id', $surat->jenis_surat_id)
            ->where('is_active', 'Y')
            ->get();

        // Kelompokkan nilai field berdasarkan id field definition
        $fieldValues = $surat->fieldValues->keyBy('field_definition_id');

        return view('pages.admin.surat-keluar.edit', compact('surat', 'fieldDefinitions', 'fieldValues', 'jenisSuratList'));
    }


    public function update(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        // Validasi field statis
        $request->validate([
            'nomor_surat'       => 'required|string|max:100',
            'tanggal_surat'     => 'required|date',
            'nama_surat'        => 'required|string|max:255',
            'jenis_surat_id'    => 'required|exists:jenis_surat,id',
            'file_lampiran'     => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        // Ambil definisi field dinamis berdasarkan jenis surat baru
        $fieldDefinitions = FieldDefinition::where('jenis_surat_id', $request->jenis_surat_id)->get();

        // Validasi dinamis
        $rules = [];
        foreach ($fieldDefinitions as $field) {
            $key = "field_values.{$field->id}";
            $rule = '';

            // Cek apakah field ini file dan apakah sudah ada file lama-nya
            $existingValue = FieldValue::where('surat_id', $surat->id)
                ->where('field_definition_id', $field->id)
                ->first();

            $hasOldFile = $field->tipe_input === 'file' && $existingValue && !empty($existingValue->value);

            // Atur required hanya jika tidak ada file lama
            if ($field->is_required === 'Y') {
                if ($field->tipe_input === 'file') {
                    $rule = $hasOldFile ? 'nullable' : 'required';
                } else {
                    $rule = 'required';
                }
            } else {
                $rule = 'nullable';
            }

            // Tambahkan validasi sesuai tipe
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
                case 'file':
                    $rule .= '|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048';
                    break;
            }

            $rules[$key] = $rule;
        }

        $request->validate($rules);

        DB::beginTransaction();

        try {
            // Update data statis
            $surat->nomor_surat = $request->nomor_surat;
            $surat->tanggal_surat = $request->tanggal_surat;
            $surat->nama_surat = $request->nama_surat;
            $surat->jenis_surat_id = $request->jenis_surat_id;

            // Buat folder tujuan file
            $folder = "assets/lampiran/surat-keluar/{$surat->id}";
            $destinationPath = public_path($folder);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            // Update file lampiran utama jika ada
            if ($request->hasFile('file_lampiran')) {
                // Hapus file lama jika ada
                $oldPath = public_path($surat->file_lampiran);
                if ($surat->file_lampiran && file_exists($oldPath)) {
                    unlink($oldPath);
                }

                $file = $request->file('file_lampiran');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destinationPath, $filename);

                $surat->file_lampiran = "{$folder}/{$filename}";
            }

            $surat->save();

            $inputFieldValues = $request->input('field_values', []);

            foreach ($fieldDefinitions as $field) {
                $value = $inputFieldValues[$field->id] ?? '';

                // Jika tipe file, handle upload file
                if ($field->tipe_input === 'file' && $request->hasFile("field_values.{$field->id}")) {
                    // Hapus file lama jika ada
                    $existing = FieldValue::where('surat_id', $surat->id)
                        ->where('field_definition_id', $field->id)
                        ->first();

                    if ($existing && $existing->value && file_exists(public_path($existing->value))) {
                        unlink(public_path($existing->value));
                    }

                    $uploadedFile = $request->file("field_values.{$field->id}");
                    $filename = time() . '_' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();
                    $uploadedFile->move($destinationPath, $filename);

                    $value = "{$folder}/{$filename}";
                }

                // Simpan/update field value
                FieldValue::updateOrCreate(
                    ['surat_id' => $surat->id, 'field_definition_id' => $field->id],
                    ['value' => $value]
                );
            }

            DB::commit();

            $redirectPath = Session('user')['role'] === 'admin'
                ? '/admin/surat-keluar'
                : '/staff/surat-keluar';

            return redirect($redirectPath)->with('success', 'Surat keluar berhasil diperbarui.');
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
            Notifikasi::where('surat_id', $surat->id)->delete();

            FieldValue::where('surat_id', $surat->id)->delete();
            $surat->delete();

            DB::commit();

            // return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
            if (Session('user')['role'] == 'admin') {
                return redirect('/admin/surat-keluar')->with('success', 'Surat keluar berhasil dihapus.');
            } else {
                return redirect('/staff/surat-keluar')->with('success', 'Surat keluar berhasil dihapus.');
            }
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

        return view('pages.admin.surat-keluar.show', compact('surat', 'fieldDefinitions', 'fieldValues'));
    }

    public function download($id)
    {
        $surat = Surat::with(['jenisSurat', 'fieldValues.fieldDefinition'])->findOrFail($id);
        $user = User::where('role', 'kepala desa')->first();
        // Mapping field dinamis dengan label-nya
        $fields = $surat->fieldValues->map(function ($fv) {
            return [
                'label' => $fv->fieldDefinition->label,
                'value' => $fv->value,
                'tipe_input' => $fv->fieldDefinition->tipe_input,
            ];
        });


        // URL file lampiran jika ada
        $lampiranUrl = $surat->file_lampiran
            ? url('public/storage/' . $surat->file_lampiran)
            : null;

        // dd($fields);
        // Kirim data ke view untuk PDF
        $pdf = Pdf::loadView('pages.admin.surat-keluar.download', [
            'surat' => $surat,
            'fields' => $fields,
            'lampiranUrl' => $lampiranUrl,
            'user' => $user,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('surat-keterangan-kematian.pdf');

        // return view('pages.admin.surat-masuk.download', compact('surat', 'fields', 'lampiranUrl'));

        // $pdf = PDF::loadView('pages.admin.surat-masuk.download');
        // $pdf->setPaper('A4', 'portrait');

        // return $pdf->stream('surat-keterangan-kematian.pdf');
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
