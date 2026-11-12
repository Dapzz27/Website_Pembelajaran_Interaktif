<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaMateriProgress extends Model
{
    protected $table = 'siswa_materi_progress';

    protected $fillable = [
        'siswa_id',
        'materi_id',
        'progress',
        'status',
        'baca_selesai',
        'jawaban_studi_kasus',
        'completed_at',
    ];

    protected $casts = [
        'progress' => 'integer',
        'baca_selesai' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }
}