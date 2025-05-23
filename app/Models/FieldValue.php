<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FieldValue extends Model
{
    protected $table = 'field_values';

    protected $fillable = [
        'surat_id',
        'field_definition_id',
        'value',
    ];

    // Relasi: FieldValue milik satu Surat
    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    // Relasi: FieldValue milik satu FieldDefinition
    public function fieldDefinition()
    {
        return $this->belongsTo(FieldDefinition::class, 'field_definition_id');
    }
}
