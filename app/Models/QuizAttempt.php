<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    protected $table = 'quiz_attempts';

    protected $fillable = [
        'user_id',
        'quiz_id',
        'skor',
        'jawaban_benar',
        'jawaban_salah',
        'total_soal',
        'detail_jawaban',
        'durasi_detik',
    ];

    protected $casts = [
        'skor' => 'integer',
        'jawaban_benar' => 'integer',
        'jawaban_salah' => 'integer',
        'total_soal' => 'integer',
        'durasi_detik' => 'integer',
        'detail_jawaban' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
