<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    protected $table = 'modul';

    protected $fillable = [
        'judul_modul',
        'kategori_atau_kelas',
        'deskripsi_singkat',
        'thumbnail_atau_ikon',
        'urutan_modul',
        'created_by',
    ];

    protected $casts = [
        'urutan_modul' => 'integer',
        'created_by' => 'integer',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function materi()
    {
        return $this->hasMany(Materi::class, 'modul_id')
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc');
    }
}
