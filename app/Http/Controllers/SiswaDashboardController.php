<?php

namespace App\Http\Controllers;

use App\Models\RiwayatBelajar;
use Illuminate\Support\Facades\Auth;

class SiswaDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $stats = $user->getLearningStats();

        /** @var SiswaMateriController $materiCtrl */
        $materiCtrl = app(SiswaMateriController::class);
        $modulList = $materiCtrl->getModulCatalogWithProgress($user->id);

        // Semua materi terurut lintas modul untuk menentukan materi aktif yang sedang dipelajari
        $materi = $modulList->flatMap(fn ($mod) => $mod->materi_list)->values();

        // Modul yang sedang dipelajari (belum selesai 100% dan punya materi) atau modul pertama
        $activeModul = $modulList->first(fn ($mod) => $mod->jumlah_materi > 0 && !$mod->is_completed)
            ?? $modulList->first();

        // Materi yang sedang terbuka di dalam modul aktif
        $activeQuest = $activeModul
            ? ($activeModul->materi_list->firstWhere('lock_state', 'terbuka') ?? $activeModul->materi_list->last())
            : ($materi->firstWhere('lock_state', 'terbuka') ?? $materi->last());

        $modulAktif = $materi;

        $riwayatTerbaru = RiwayatBelajar::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('siswa.dashboard', compact(
            'stats',
            'modulList',
            'activeModul',
            'materi',
            'activeQuest',
            'modulAktif',
            'riwayatTerbaru'
        ));
    }
}
