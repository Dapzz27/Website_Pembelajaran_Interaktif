<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogBacaMateri extends Model
{
    protected $table = 'log_baca_materi';

    protected $fillable = [
        'siswa_id',
        'materi_id',
        'bagian_terbaca_json',
        'status_baca',
        'waktu_selesai_baca',
    ];

    protected $casts = [
        'waktu_selesai_baca' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function getBagianTerbacaArrayAttribute(): array
    {
        if (empty($this->bagian_terbaca_json)) {
            return [];
        }
        $decoded = json_decode($this->bagian_terbaca_json, true);
        return is_array($decoded) ? $decoded : [];
    }
}
