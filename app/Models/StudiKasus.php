<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudiKasus extends Model
{
    protected $table = 'studi_kasus';

    protected $fillable = [
        'materi_id',
        'judul_studi_kasus',
        'tipe_studi_kasus',
        'instruksi_soal',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'kunci_jawaban',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function pengumpulan()
    {
        return $this->hasMany(PengumpulanStudiKasus::class, 'studi_kasus_id');
    }
}
