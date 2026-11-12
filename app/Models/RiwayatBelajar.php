<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatBelajar extends Model
{
    protected $table = 'riwayat_belajar';

    protected $fillable = [
        'user_id',
        'tipe_aktivitas',
        'judul_aktivitas',
        'keterangan',
        'skor',
        'referensi_id',
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
