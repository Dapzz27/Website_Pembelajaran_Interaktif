<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'nomor_induk',
        'email',
        'role',
        'password',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'integer',
    ];

    public function modul()
    {
        return $this->hasMany(Modul::class, 'created_by');
    }

    public function materi()
    {
        return $this->hasMany(Materi::class, 'guru_id');
    }

    public function progressMateri()
    {
        return $this->hasMany(SiswaMateriProgress::class, 'siswa_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    public function riwayatBelajar()
    {
        return $this->hasMany(RiwayatBelajar::class, 'user_id');
    }

    /**
     * Hitung seluruh metrik progres & Level siswa secara real-time dari database.
     * Siswa baru dimulai dari Level 0 dan seluruh indikator bernilai 0.
     */
    public function getLearningStats(): array
    {
        $hasModulTable = Schema::hasTable('modul');
        $totalModul = $hasModulTable ? Modul::count() : 0;

        $validMateri = Materi::with('studiKasus')->get();
        $validMateriIds = $validMateri->pluck('id')->toArray();
        $totalMateri = count($validMateriIds);

        $totalStudiKasus = $validMateri->filter(function ($m) {
            return $m->studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus));
        })->count();

        $totalQuiz = Quiz::count();

        $allProgress = SiswaMateriProgress::where('siswa_id', $this->id)
            ->whereIn('materi_id', $validMateriIds)
            ->get();
        $completedProgress = $allProgress->where('status', 'selesai');
        $completedMateriIdsFromProg = $completedProgress->pluck('materi_id')->toArray();

        $pengumpulanMateriIds = [];
        $pengumpulanCount = 0;
        if (Schema::hasTable('pengumpulan_studi_kasus') && Schema::hasTable('studi_kasus')) {
            $pengumpulan = PengumpulanStudiKasus::where('siswa_id', $this->id)
                ->with('studiKasus')
                ->get();
            foreach ($pengumpulan as $p) {
                if ($p->studiKasus && in_array($p->studiKasus->materi_id, $validMateriIds, true)) {
                    $pengumpulanMateriIds[] = (int) $p->studiKasus->materi_id;
                }
            }
            $pengumpulanMateriIds = array_values(array_unique($pengumpulanMateriIds));
            $pengumpulanCount = count($pengumpulanMateriIds);
        }

        $completedMateriIds = array_values(array_unique(array_merge($completedMateriIdsFromProg, $pengumpulanMateriIds)));
        $materiSelesai = count($completedMateriIds);

        $studiKasusSelesai = max(
            $completedProgress->filter(fn ($p) => !empty(trim((string) $p->jawaban_studi_kasus)))->count(),
            $pengumpulanCount
        );
        $studiKasusSelesai = min($totalStudiKasus, $studiKasusSelesai);

        // Hitung modul yang seluruh materinya telah diselesaikan oleh siswa
        $modulSelesai = 0;
        if ($hasModulTable && $totalModul > 0) {
            $groupedByModul = $validMateri->groupBy('modul_id');
            foreach ($groupedByModul as $mId => $itemsInModul) {
                if (!$mId || $itemsInModul->isEmpty()) {
                    continue;
                }
                $allInModulDone = $itemsInModul->every(fn ($item) => in_array((int) $item->id, $completedMateriIds, true));
                if ($allInModulDone) {
                    $modulSelesai++;
                }
            }
        }

        // Hitung materi yang sudah dibaca tapi belum selesai studi kasus
        $materiSedangDibaca = $allProgress
            ->whereNotIn('materi_id', $completedMateriIds)
            ->where('baca_selesai', true)
            ->count();

        if (Schema::hasTable('log_baca_materi')) {
            $logSelesaiIds = LogBacaMateri::where('siswa_id', $this->id)
                ->whereIn('materi_id', $validMateriIds)
                ->where('status_baca', 'selesai')
                ->pluck('materi_id')
                ->toArray();
            $sedangDibacaViaLog = count(array_diff($logSelesaiIds, $completedMateriIds));
            $materiSedangDibaca = max($materiSedangDibaca, $sedangDibacaViaLog);
        }

        $attempts = QuizAttempt::where('user_id', $this->id)->get();
        $quizSelesai = $attempts->pluck('quiz_id')->unique()->count();

        $bestScoresPerQuiz = $attempts->groupBy('quiz_id')->map(fn ($grp) => (int) $grp->max('skor'));
        $rataSkorQuiz = $bestScoresPerQuiz->isNotEmpty()
            ? (int) round($bestScoresPerQuiz->avg())
            : 0;
        $totalSkorQuiz = (int) $bestScoresPerQuiz->sum();

        // Perhitungan Level Siswa:
        // Mulai dari Level 0.
        // Setiap 100 poin progres = +1 Level:
        // - Baca materi = +50 poin, Submit Studi Kasus = +50 poin (1 Materi+Studi Kasus tuntas = 100 poin = +1 Level)
        // - Selesaikan 1 Paket Quiz = +100 poin (+1 Level)
        $poinProgres = ($materiSelesai * 100) + ($materiSedangDibaca * 50) + ($quizSelesai * 100);
        $level = (int) floor($poinProgres / 100);
        $progressInCurrentLevel = $poinProgres % 100;

        // Total persentase ketuntasan belajar keseluruhan (0% - 100%)
        $totalTargetItem = $totalMateri + $totalQuiz;
        $totalCompletedItem = $materiSelesai + $quizSelesai;
        $progressPct = $totalTargetItem > 0
            ? (int) min(100, round((($totalCompletedItem + ($materiSedangDibaca * 0.5)) / $totalTargetItem) * 100))
            : 0;

        if ($totalTargetItem > 0 && $totalCompletedItem >= $totalTargetItem) {
            $nextLevelPct = 100;
        } elseif ($progressInCurrentLevel > 0) {
            $nextLevelPct = $progressInCurrentLevel;
        } else {
            $nextLevelPct = $progressPct;
        }

        $rankTitle = match (true) {
            $level === 0 => 'Novice Explorer (Pemula)',
            $level === 1 => 'Beginner Coder',
            $level === 2 => 'Junior Web Crafter',
            $level === 3 => 'Code Adventurer',
            $level === 4 => 'RPL Problem Solver',
            default => 'Grandmaster CodeQuest',
        };

        return [
            'level' => $level,
            'rank_title' => $rankTitle,
            'poin_progres' => $poinProgres,
            'next_level_pct' => $nextLevelPct,
            'progress_pct' => $progressPct,
            'modul_selesai' => $modulSelesai,
            'total_modul' => $totalModul,
            'materi_selesai' => $materiSelesai,
            'total_materi' => $totalMateri,
            'studi_kasus_selesai' => $studiKasusSelesai,
            'total_studi_kasus' => $totalStudiKasus,
            'quiz_selesai' => $quizSelesai,
            'total_quiz' => $totalQuiz,
            'rata_skor_quiz' => $rataSkorQuiz,
            'total_skor_quiz' => $totalSkorQuiz,
            'completed_units' => $totalCompletedItem,
            'total_units' => $totalTargetItem,
        ];
    }
}
