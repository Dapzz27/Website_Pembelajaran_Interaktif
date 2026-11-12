<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $table = 'quizzes';

    protected $fillable = [
        'guru_id',
        'materi_id',
        'judul_quiz',
        'deskripsi',
        'durasi_menit',
        'kkm',
    ];

    protected $casts = [
        'durasi_menit' => 'integer',
        'kkm' => 'integer',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    public function materi()
    {
        return $this->belongsTo(Materi::class, 'materi_id');
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'quiz_id')->orderBy('urutan', 'asc')->orderBy('id', 'asc');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'quiz_id');
    }
}
