<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FieldDefinition;

class FieldDefinitionController extends Controller
{
    public function byJenisSurat($jenisSuratId)
    {
        $fields = FieldDefinition::where('jenis_surat_id', $jenisSuratId)
            ->where('is_active', 'Y')
            ->get(['id', 'label', 'tipe_input', 'is_required']); // ambil hanya field yang dibutuhkan

        return response()->json($fields);
    }
}
