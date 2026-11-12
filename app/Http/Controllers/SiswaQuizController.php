<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\RiwayatBelajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaQuizController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = $user->getLearningStats();

        $quizzes = Quiz::with([
            'questions',
            'attempts' => function ($q) use ($user) {
                $q->where('user_id', $user->id)->orderBy('created_at', 'desc');
            }
        ])->orderBy('id', 'asc')->get()->map(function ($quiz) {
            $attempts = $quiz->attempts;
            $quiz->sudah_dikerjakan = $attempts->isNotEmpty();
            $quiz->nilai_tertinggi = $attempts->isNotEmpty() ? (int) $attempts->max('skor') : null;
            $quiz->jumlah_percobaan = $attempts->count();
            $quiz->total_bobot = (int) $quiz->questions->sum('bobot_nilai');

            // Siapkan struktur soal untuk arena interaktif Alpine.js
            $quiz->arena_questions = $quiz->questions->map(function ($q, $idx) {
                return [
                    'id' => $q->id,
                    'nomor' => $idx + 1,
                    'pertanyaan' => $q->pertanyaan,
                    'opsi' => [
                        'a' => $q->opsi_a,
                        'b' => $q->opsi_b,
                        'c' => $q->opsi_c,
                        'd' => $q->opsi_d,
                    ],
                    'bobot_nilai' => (int) $q->bobot_nilai,
                ];
            })->values();

            return $quiz;
        });

        return view('siswa.quiz', compact('quizzes', 'stats'));
    }

    public function submit(Request $request, $id)
    {
        $user = Auth::user();
        $quiz = Quiz::with('questions')->findOrFail($id);
        $questions = $quiz->questions;

        if ($questions->isEmpty()) {
            return back()->with('error', 'Quiz ini belum memiliki soal.');
        }

        $statsBefore = $user->getLearningStats();
        $answers = $request->input('answers', []);
        if (is_string($answers)) {
            $answers = json_decode($answers, true) ?? [];
        }

        $totalBobot = (int) $questions->sum('bobot_nilai');
        $poinDiperoleh = 0;
        $benar = 0;
        $salah = 0;
        $detail = [];

        foreach ($questions as $q) {
            $pilihanSiswa = isset($answers[$q->id]) ? strtolower(trim((string) $answers[$q->id])) : null;
            $kunci = strtolower(trim((string) $q->jawaban_benar));
            $isCorrect = $pilihanSiswa !== null && $pilihanSiswa === $kunci;

            if ($isCorrect) {
                $benar++;
                $poinDiperoleh += (int) $q->bobot_nilai;
            } else {
                $salah++;
            }

            $detail[] = [
                'question_id' => $q->id,
                'jawaban_siswa' => $pilihanSiswa,
                'jawaban_benar' => $kunci,
                'benar' => $isCorrect,
                'bobot' => (int) $q->bobot_nilai,
            ];
        }

        $skor = $totalBobot > 0
            ? (int) round(($poinDiperoleh / $totalBobot) * 100)
            : (int) round(($benar / max(1, $questions->count())) * 100);

        $attempt = QuizAttempt::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz->id,
            'skor' => $skor,
            'jawaban_benar' => $benar,
            'jawaban_salah' => $salah,
            'total_soal' => $questions->count(),
            'detail_jawaban' => $detail,
            'durasi_detik' => $request->input('durasi_detik') ? (int) $request->input('durasi_detik') : null,
        ]);

        RiwayatBelajar::create([
            'user_id' => $user->id,
            'tipe_aktivitas' => 'quiz',
            'judul_aktivitas' => "Menyelesaikan {$quiz->judul_quiz}",
            'keterangan' => "Skor: {$skor}/100 ({$benar} Benar, {$salah} Salah)",
            'skor' => $skor,
            'referensi_id' => $quiz->id,
        ]);

        $statsAfter = $user->getLearningStats();

        $resultPayload = [
            'quiz_id' => $quiz->id,
            'judul_quiz' => $quiz->judul_quiz,
            'skor' => $skor,
            'kkm' => (int) $quiz->kkm,
            'lulus' => $skor >= (int) $quiz->kkm,
            'benar' => $benar,
            'salah' => $salah,
            'total_soal' => $questions->count(),
            'poin_diperoleh' => $poinDiperoleh,
            'total_bobot' => $totalBobot,
            'prev_level' => $statsBefore['level'],
            'new_level' => $statsAfter['level'],
            'rank_title' => $statsAfter['rank_title'],
            'level_naik' => $statsAfter['level'] > $statsBefore['level'],
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'result' => $resultPayload,
                'stats' => $statsAfter,
            ]);
        }

        return redirect()
            ->route('siswa.quiz')
            ->with('quiz_result', $resultPayload);
    }
}
