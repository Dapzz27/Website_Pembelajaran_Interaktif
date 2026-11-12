<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\RiwayatBelajar;
use Illuminate\Support\Facades\Auth;

class SiswaStatistikController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = $user->getLearningStats();

        /** @var SiswaMateriController $materiCtrl */
        $materiCtrl = app(SiswaMateriController::class);
        $modulList = $materiCtrl->getModulCatalogWithProgress($user->id);

        $materiList = $modulList->flatMap(fn ($mod) => $mod->materi_list)->values();

        // Daftar Studi Kasus yang Belum Dikerjakan
        $studiKasusBelum = $materiList->filter(function ($m) {
            $hasKasus = $m->studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus));
            return $hasKasus && $m->lock_state !== 'selesai';
        })->values();

        // Data Quiz & Skor Tertinggi Siswa untuk Grafik
        $quizzes = Quiz::with([
            'attempts' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }
        ])->orderBy('id', 'asc')->get();

        $quizChartLabels = [];
        $quizChartScores = [];
        foreach ($quizzes as $q) {
            $quizChartLabels[] = $q->judul_quiz;
            $quizChartScores[] = $q->attempts->isNotEmpty() ? (int) $q->attempts->max('skor') : 0;
        }

        $materiChartLabels = [];
        $materiChartProgress = [];
        foreach ($materiList as $m) {
            $materiChartLabels[] = $m->judul_materi ?: $m->judul;
            $materiChartProgress[] = (int) $m->progress_pct;
        }

        // Riwayat Pembelajaran
        $riwayat = RiwayatBelajar::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(25)
            ->get();

        return view('siswa.statistik', compact(
            'stats',
            'modulList',
            'materiList',
            'studiKasusBelum',
            'quizChartLabels',
            'quizChartScores',
            'materiChartLabels',
            'materiChartProgress',
            'riwayat'
        ));
    }
}
