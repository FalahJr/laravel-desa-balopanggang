<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldDefinition extends Model
{
    protected $table = 'field_definitions';

    protected $fillable = [
        'jenis_surat_id',
        'nama_field',
        'label',
        'tipe_input',
        'is_required',
        'is_active',
    ];

    protected $casts = [
        // 'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi: FieldDefinition milik satu JenisSurat
    public function jenisSurat()
    {
        return $this->belongsTo(JenisSurat::class);
    }

    // Relasi: FieldDefinition punya banyak FieldValue
    public function fieldValues()
    {
        return $this->hasMany(FieldValue::class);
    }
}
