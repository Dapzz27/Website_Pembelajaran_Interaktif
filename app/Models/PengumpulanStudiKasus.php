<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengumpulanStudiKasus extends Model
{
    protected $table = 'pengumpulan_studi_kasus';

    protected $fillable = [
        'studi_kasus_id',
        'siswa_id',
        'jawaban_teks',
        'file_lampiran',
        'nama_file_asli',
        'tipe_file',
        'tanggal_kumpul',
        'status',
    ];

    protected $casts = [
        'tanggal_kumpul' => 'datetime',
    ];

    public function studiKasus()
    {
        return $this->belongsTo(StudiKasus::class, 'studi_kasus_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function getFileUrlAttribute(): ?string
    {
        if (empty($this->file_lampiran)) {
            return null;
        }
        return asset($this->file_lampiran);
    }
}
