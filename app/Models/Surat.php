<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Surat extends Model
{
    protected $table = 'surat';

    protected $fillable = [
        'tipe_surat',
        'jenis_surat_id',
        'nomor_surat',
        'nama_surat',
        'tanggal_surat',
        'file_lampiran',
        'status'
    ];

    protected $dates = ['tanggal_surat'];

    // Relasi: Surat milik satu JenisSurat
    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    // Relasi: Surat punya banyak FieldValue (isian dinamis)
    public function fieldValues()
    {
        return $this->hasMany(FieldValue::class);
    }
}
