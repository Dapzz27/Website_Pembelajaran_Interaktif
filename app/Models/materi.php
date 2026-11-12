<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $table = 'materi';

    protected $fillable = [
        'modul_id',
        'guru_id',
        'created_by',
        'judul',
        'judul_materi',
        'bab',
        'sub_bab',
        'kelas',
        'deskripsi',
        'konten_materi',
        'has_video',
        'video_url',
        'tipe_studi_kasus',
        'soal_studi_kasus',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'kunci_studi_kasus',
        'urutan',
    ];

    protected $casts = [
        'modul_id' => 'integer',
        'has_video' => 'boolean',
        'urutan' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function (Materi $materi) {
            if (!empty($materi->judul_materi) && empty($materi->judul)) {
                $materi->judul = $materi->judul_materi;
            } elseif (!empty($materi->judul) && empty($materi->judul_materi)) {
                $materi->judul_materi = $materi->judul;
            }

            if (!empty($materi->konten_materi) && empty($materi->deskripsi)) {
                $materi->deskripsi = $materi->konten_materi;
            } elseif (!empty($materi->deskripsi) && empty($materi->konten_materi)) {
                $materi->konten_materi = $materi->deskripsi;
            }

            if (!empty($materi->guru_id) && empty($materi->created_by)) {
                $materi->created_by = $materi->guru_id;
            } elseif (!empty($materi->created_by) && empty($materi->guru_id)) {
                $materi->guru_id = $materi->created_by;
            }
        });
    }

    public function modul()
    {
        return $this->belongsTo(Modul::class, 'modul_id');
    }

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function bagianMateri()
    {
        return $this->hasMany(BagianMateri::class, 'materi_id')->orderBy('urutan_blok', 'asc')->orderBy('id', 'asc');
    }

    public function studiKasus()
    {
        return $this->hasOne(StudiKasus::class, 'materi_id')->latestOfMany();
    }

    public function logBaca()
    {
        return $this->hasMany(LogBacaMateri::class, 'materi_id');
    }

    public function progressSiswa()
    {
        return $this->hasMany(SiswaMateriProgress::class, 'materi_id');
    }

    /**
     * Ubah URL YouTube biasa menjadi embed URL.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }
        $url = trim($this->video_url);
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?rel=0';
        }
        return null;
    }

    public function getYoutubeWatchUrlAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }
        $url = trim($this->video_url);
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return 'https://www.youtube.com/watch?v=' . $matches[1];
        }
        return $url;
    }
}
