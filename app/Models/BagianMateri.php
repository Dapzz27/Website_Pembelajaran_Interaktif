<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BagianMateri extends Model
{
    protected $table = 'bagian_materi';

    protected $fillable = [
        'materi_id',
        'tipe_layout',
        'sub_judul',
        'isi_teks',
        'tipe_media',
        'file_gambar',
        'url_video',
        'urutan_blok',
    ];

    protected $casts = [
        'urutan_blok' => 'integer',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    /**
     * Ubah URL YouTube biasa menjadi embed URL untuk iframe aspect-video.
     */
    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (empty($this->url_video)) {
            return null;
        }
        $url = trim($this->url_video);
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1] . '?rel=0';
        }
        return null;
    }

    /**
     * Dapatkan URL langsung ke YouTube untuk dibuka di tab baru.
     */
    public function getYoutubeWatchUrlAttribute(): ?string
    {
        if (empty($this->url_video)) {
            return null;
        }
        $url = trim($this->url_video);
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/shorts\/)([A-Za-z0-9_-]{6,})/', $url, $matches)) {
            return 'https://www.youtube.com/watch?v=' . $matches[1];
        }
        return $url;
    }

    public function getGambarUrlAttribute(): ?string
    {
        if (empty($this->file_gambar)) {
            return null;
        }
        if (str_starts_with($this->file_gambar, 'http://') || str_starts_with($this->file_gambar, 'https://')) {
            return $this->file_gambar;
        }
        return asset($this->file_gambar);
    }
}
