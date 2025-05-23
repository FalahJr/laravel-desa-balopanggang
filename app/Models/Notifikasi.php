<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $id
 * @property integer $user_id
 * @property string $judul
 * @property string $deskripsi
 * @property string $is_seen
 * @property string $created_at
 * @property string $updated_at
 * @property User $user
 */
class Notifikasi extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'notifikasi';

    /**
     * @var array
     */
    protected $fillable = ['role', 'judul', 'deskripsi', 'is_seen', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
