<?php

namespace App\Http\Controllers;

use App\Models\BagianMateri;
use App\Models\LogBacaMateri;
use App\Models\Materi;
use App\Models\Modul;
use App\Models\PengumpulanStudiKasus;
use App\Models\RiwayatBelajar;
use App\Models\SiswaMateriProgress;
use App\Models\StudiKasus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SiswaMateriController extends Controller
{
    /**
     * Pastikan tidak ada materi yatim tanpa modul_id.
     */
    protected function ensureOrphanMateriLinked(): void
    {
        if (Materi::whereNull('modul_id')->exists()) {
            $firstModul = Modul::orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->first();
            if (!$firstModul) {
                $firstModul = Modul::create([
                    'judul_modul' => 'Pemrograman Web Dasar',
                    'kategori_atau_kelas' => 'Kelas X / XI RPL',
                    'deskripsi_singkat' => 'Fondasi pengembangan antarmuka web modern mencakup struktur dokumen HTML, penggunaan tag dan atribut, elemen semantik, serta studi kasus pembuatan halaman web.',
                    'urutan_modul' => 1,
                ]);
            }
            Materi::whereNull('modul_id')->update(['modul_id' => $firstModul->id]);
        }
    }

    /**
     * Ambil daftar modul beserta daftar sub-bab materi di dalamnya dengan
     * logika Gembok Berurutan PER MODUL (Sequential Unlocking per Modul).
     */
    public function getModulCatalogWithProgress($userId)
    {
        $this->ensureOrphanMateriLinked();

        $moduls = Modul::with([
            'materi' => function ($q) {
                $q->orderBy('urutan', 'asc')->orderBy('id', 'asc');
            },
            'materi.modul',
            'materi.guru',
            'materi.bagianMateri',
            'materi.studiKasus.pengumpulan' => function ($q) use ($userId) {
                $q->where('siswa_id', $userId)->latest();
            },
            'materi.logBaca' => function ($q) use ($userId) {
                $q->where('siswa_id', $userId);
            },
            'materi.progressSiswa' => function ($q) use ($userId) {
                $q->where('siswa_id', $userId);
            },
        ])->orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->get()->values();

        return $moduls->map(function ($modul) {
            $items = $modul->materi->values();

            $mappedMateri = $items->map(function ($m, $idx) use ($items) {
                $prog = $m->progressSiswa->first();
                $logBaca = $m->logBaca->first();
                $studiKasus = $m->studiKasus;
                $hasKasus = $studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus));
                $pengumpulan = $studiKasus ? $studiKasus->pengumpulan->first() : null;

                $bacaSelesai = ($prog && (bool) $prog->baca_selesai)
                    || ($logBaca && $logBaca->status_baca === 'selesai');

                $isCompleted = ($prog && $prog->status === 'selesai')
                    || ($pengumpulan !== null)
                    || (!$hasKasus && $bacaSelesai);

                if ($isCompleted) {
                    $bacaSelesai = true;
                }

                // Logika Gembok per Modul:
                // Materi urutan ke-1 ($idx === 0) di dalam modul SELALU terbuka.
                // Materi urutan ke-2 dst. terkunci hingga materi urutan sebelumnya di modul yang sama selesai.
                if ($idx === 0) {
                    $lockState = $isCompleted ? 'selesai' : 'terbuka';
                    $prevJudul = null;
                } else {
                    $prevItem = $items[$idx - 1];
                    $prevProg = $prevItem->progressSiswa->first();
                    $prevLog = $prevItem->logBaca->first();
                    $prevKasus = $prevItem->studiKasus;
                    $prevHasKasus = $prevKasus !== null || !empty(trim((string) $prevItem->tipe_studi_kasus));
                    $prevPengumpulan = $prevKasus ? $prevKasus->pengumpulan->first() : null;
                    $prevBacaDone = ($prevProg && (bool) $prevProg->baca_selesai)
                        || ($prevLog && $prevLog->status_baca === 'selesai');
                    $prevCompleted = ($prevProg && $prevProg->status === 'selesai')
                        || ($prevPengumpulan !== null)
                        || (!$prevHasKasus && $prevBacaDone);

                    $lockState = !$prevCompleted ? 'terkunci' : ($isCompleted ? 'selesai' : 'terbuka');
                    $prevJudul = $prevItem->judul_materi ?: $prevItem->judul;
                }

                $m->lock_state = $lockState;
                $m->is_unlocked = ($lockState !== 'terkunci');
                $m->baca_selesai = $bacaSelesai;
                $m->progress_pct = $isCompleted ? 100 : ($bacaSelesai ? 50 : 0);
                $m->jawaban_siswa = $pengumpulan?->jawaban_teks ?? ($prog?->jawaban_studi_kasus ?? '');
                $m->pengumpulan_siswa = $pengumpulan;
                $m->completed_at = $pengumpulan?->tanggal_kumpul
                    ? $pengumpulan->tanggal_kumpul->translatedFormat('d M Y, H:i')
                    : ($prog?->completed_at ? $prog->completed_at->translatedFormat('d M Y, H:i') : null);
                $m->prev_judul = $prevJudul;
                $m->urutan_dalam_modul = $idx + 1;

                return $m;
            });

            $totalMateri = $mappedMateri->count();
            $materiSelesai = $mappedMateri->where('lock_state', 'selesai')->count();
            $materiSedangDibaca = $mappedMateri->filter(fn ($m) => $m->lock_state !== 'selesai' && $m->baca_selesai)->count();
            $totalStudiKasus = $mappedMateri->filter(fn ($m) => $m->studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus)))->count();
            $studiKasusSelesai = $mappedMateri->filter(fn ($m) => ($m->studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus))) && $m->lock_state === 'selesai')->count();

            $progressPct = $totalMateri > 0
                ? (int) min(100, round((($materiSelesai + ($materiSedangDibaca * 0.5)) / $totalMateri) * 100))
                : 0;

            $modul->materi_list = $mappedMateri;
            $modul->jumlah_materi = $totalMateri;
            $modul->materi_selesai = $materiSelesai;
            $modul->jumlah_studi_kasus = $totalStudiKasus;
            $modul->studi_kasus_selesai = $studiKasusSelesai;
            $modul->progress_pct = $progressPct;
            $modul->is_completed = $totalMateri > 0 && $materiSelesai >= $totalMateri;

            return $modul;
        });
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $modulList = $this->getModulCatalogWithProgress($user->id);
        $stats = $user->getLearningStats();

        if ($request->filled('buka')) {
            $targetId = (int) $request->query('buka');
            foreach ($modulList as $mod) {
                $target = $mod->materi_list->firstWhere('id', $targetId);
                if ($target && $target->lock_state !== 'terkunci') {
                    return redirect()->route('siswa.materi.show', $target->id);
                }
            }
        }

        $selectedModulId = $request->filled('modul_id') ? (int) $request->query('modul_id') : null;
        $activeModul = $selectedModulId
            ? $modulList->firstWhere('id', $selectedModulId)
            : null;

        $materiList = $activeModul
            ? $activeModul->materi_list
            : $modulList->flatMap(fn ($mod) => $mod->materi_list)->values();

        return view('siswa.materi', compact(
            'modulList',
            'activeModul',
            'materiList',
            'stats'
        ));
    }

    /**
     * Halaman Detail Materi Siswa (Full Page View, Clean & Seamless).
     */
    public function show($id)
    {
        $user = Auth::user();
        $modulList = $this->getModulCatalogWithProgress($user->id);

        $activeModul = null;
        $materi = null;
        $targetIndex = false;

        foreach ($modulList as $mod) {
            $idx = $mod->materi_list->search(fn ($m) => (int) $m->id === (int) $id);
            if ($idx !== false) {
                $activeModul = $mod;
                $materi = $mod->materi_list[$idx];
                $targetIndex = $idx;
                break;
            }
        }

        if (!$materi || !$activeModul) {
            return redirect()->route('siswa.materi')->with('error', 'Materi tidak ditemukan.');
        }

        $materiList = $activeModul->materi_list;

        if ($materi->lock_state === 'terkunci') {
            return redirect()->route('siswa.materi', ['modul_id' => $activeModul->id])
                ->with('error', 'Materi "' . ($materi->judul_materi ?: $materi->judul) . '" masih terkunci. Selesaikan "' . $materi->prev_judul . '" terlebih dahulu.');
        }

        // Pastikan materi memiliki minimal 1 blok di `bagian_materi`
        if ($materi->bagianMateri->isEmpty()) {
            $hasVid = (bool) $materi->has_video || !empty($materi->video_url);
            BagianMateri::create([
                'materi_id' => $materi->id,
                'tipe_layout' => $hasVid ? 'media_kiri' : 'teks_penuh',
                'sub_judul' => $materi->judul_materi ?: $materi->judul,
                'isi_teks' => $materi->konten_materi ?: $materi->deskripsi ?: 'Pelajari materi ini dengan saksama.',
                'tipe_media' => $hasVid ? 'video' : 'none',
                'url_video' => $hasVid ? ($materi->video_url ?: 'https://www.youtube.com/watch?v=UB1O30fR-EE') : null,
                'urutan_blok' => 1,
            ]);
            $materi->load('bagianMateri');
        }

        // Pastikan record `studi_kasus` sinkron jika ada tipe_studi_kasus di tabel materi
        if (!$materi->studiKasus && !empty(trim((string) $materi->tipe_studi_kasus))) {
            StudiKasus::create([
                'materi_id' => $materi->id,
                'judul_studi_kasus' => 'Studi Kasus: ' . ($materi->judul_materi ?: $materi->judul),
                'tipe_studi_kasus' => $materi->tipe_studi_kasus,
                'instruksi_soal' => $materi->soal_studi_kasus ?: 'Selesaikan tantangan studi kasus berdasarkan materi di atas.',
                'opsi_a' => $materi->opsi_a,
                'opsi_b' => $materi->opsi_b,
                'opsi_c' => $materi->opsi_c,
                'opsi_d' => $materi->opsi_d,
                'kunci_jawaban' => $materi->kunci_studi_kasus,
            ]);
            $materi->load('studiKasus');
        }

        $logBaca = LogBacaMateri::firstOrCreate(
            ['siswa_id' => $user->id, 'materi_id' => $materi->id],
            ['bagian_terbaca_json' => json_encode([]), 'status_baca' => $materi->baca_selesai ? 'selesai' : 'sedang_dibaca']
        );

        $terbacaList = $logBaca->bagian_terbaca_array;
        $studiKasus = $materi->studiKasus;
        $pengumpulan = $studiKasus
            ? PengumpulanStudiKasus::where('studi_kasus_id', $studiKasus->id)->where('siswa_id', $user->id)->latest()->first()
            : null;

        $prevMateri = $targetIndex > 0 ? $materiList[$targetIndex - 1] : null;
        $nextMateri = isset($materiList[$targetIndex + 1]) ? $materiList[$targetIndex + 1] : null;
        $stats = $user->getLearningStats();

        return view('siswa.materi_detail', compact(
            'activeModul',
            'materi',
            'materiList',
            'studiKasus',
            'pengumpulan',
            'logBaca',
            'terbacaList',
            'prevMateri',
            'nextMateri',
            'stats'
        ));
    }

    /**
     * Endpoint Real-Time Auto-Read & Media Tracking (AJAX).
     */
    public function trackBaca(Request $request, $id)
    {
        $user = Auth::user();
        $modulList = $this->getModulCatalogWithProgress($user->id);
        $target = null;

        foreach ($modulList as $mod) {
            $found = $mod->materi_list->firstWhere('id', (int) $id);
            if ($found) {
                $target = $found;
                break;
            }
        }

        if (!$target || $target->lock_state === 'terkunci') {
            return response()->json(['message' => 'Materi masih terkunci.'], 403);
        }

        $log = LogBacaMateri::firstOrNew([
            'siswa_id' => $user->id,
            'materi_id' => $target->id,
        ]);

        $existingKeys = $log->bagian_terbaca_array;
        $incomingKeys = $request->input('completed_keys', []);
        if (is_string($incomingKeys)) {
            $incomingKeys = json_decode($incomingKeys, true) ?? [];
        }
        if ($request->filled('item_key')) {
            $incomingKeys[] = (string) $request->input('item_key');
        }

        $mergedKeys = array_values(array_unique(array_merge($existingKeys, $incomingKeys)));

        $blocks = BagianMateri::where('materi_id', $target->id)->orderBy('urutan_blok', 'asc')->get();
        $requiredKeys = [];
        foreach ($blocks as $b) {
            $requiredKeys[] = 'block_' . $b->id;
            if ($b->tipe_media === 'gambar' && !empty($b->file_gambar)) {
                $requiredKeys[] = 'media_' . $b->id;
            } elseif ($b->tipe_media === 'video' && !empty($b->url_video)) {
                $requiredKeys[] = 'media_' . $b->id;
            }
        }

        $missing = array_diff($requiredKeys, $mergedKeys);
        $allCompleted = empty($missing) || $request->boolean('all_completed');

        if ($allCompleted) {
            $mergedKeys = array_values(array_unique(array_merge($mergedKeys, $requiredKeys)));
        }

        $wasFinished = $log->status_baca === 'selesai';
        $log->bagian_terbaca_json = json_encode($mergedKeys);

        if ($allCompleted) {
            $log->status_baca = 'selesai';
            if (!$log->waktu_selesai_baca) {
                $log->waktu_selesai_baca = now();
            }
        } elseif (!$wasFinished) {
            $log->status_baca = 'sedang_dibaca';
        }
        $log->save();

        if ($allCompleted) {
            $prog = SiswaMateriProgress::firstOrNew([
                'siswa_id' => $user->id,
                'materi_id' => $target->id,
            ]);
            $wasProgRead = (bool) $prog->baca_selesai;
            $prog->baca_selesai = true;

            $hasStudiKasus = $target->studiKasus !== null || !empty(trim((string) $target->tipe_studi_kasus));
            if (!$hasStudiKasus) {
                $prog->status = 'selesai';
                $prog->progress = 100;
                $prog->completed_at = $prog->completed_at ?: now();
            } elseif ($prog->status !== 'selesai') {
                $prog->status = 'aktif';
                $prog->progress = max((int) $prog->progress, 50);
            }
            $prog->save();

            if (!$wasFinished && !$wasProgRead) {
                $judul = $target->judul_materi ?: $target->judul;
                RiwayatBelajar::create([
                    'user_id' => $user->id,
                    'tipe_aktivitas' => 'materi',
                    'judul_aktivitas' => "Menyelesaikan bacaan materi: {$judul}",
                    'keterangan' => "{$target->bab} · {$target->sub_bab}",
                    'referensi_id' => $target->id,
                ]);
            }
        }

        $totalReq = count($requiredKeys);
        $doneReq = count(array_intersect($requiredKeys, $mergedKeys));
        $pct = $totalReq > 0 ? (int) min(100, round(($doneReq / $totalReq) * 100)) : 100;

        return response()->json([
            'success' => true,
            'status_baca' => $log->status_baca,
            'baca_selesai' => $log->status_baca === 'selesai',
            'progress_baca_pct' => $log->status_baca === 'selesai' ? 100 : $pct,
            'completed_keys' => $mergedKeys,
            'stats' => $user->getLearningStats(),
        ]);
    }

    public function tandaiBaca(Request $request, $id)
    {
        $request->merge(['all_completed' => true]);
        return $this->trackBaca($request, $id);
    }

    /**
     * Pengumpulan Studi Kasus Siswa (Teks/Kode/Pilihan + Upload File Lampiran).
     */
    public function selesaikanMisi(Request $request, $id)
    {
        $user = Auth::user();
        $modulList = $this->getModulCatalogWithProgress($user->id);

        $activeModul = null;
        $target = null;
        $targetIndex = false;

        foreach ($modulList as $mod) {
            $idx = $mod->materi_list->search(fn ($m) => (int) $m->id === (int) $id);
            if ($idx !== false) {
                $activeModul = $mod;
                $target = $mod->materi_list[$idx];
                $targetIndex = $idx;
                break;
            }
        }

        if (!$target || !$activeModul) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Materi tidak ditemukan.'], 404);
            }
            return back()->with('error', 'Materi tidak ditemukan.');
        }

        if ($target->lock_state === 'terkunci') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Materi ini masih terkunci!'], 403);
            }
            return back()->with('error', 'Materi ini masih terkunci! Selesaikan materi sebelumnya terlebih dahulu.');
        }

        $jawabanInput = trim((string) $request->input('jawaban_studi_kasus', $request->input('jawaban_teks', '')));
        $hasFile = $request->hasFile('file_lampiran');
        $hasStudiKasus = $target->studiKasus !== null || !empty(trim((string) $target->tipe_studi_kasus));

        if ($hasStudiKasus && $jawabanInput === '' && !$hasFile) {
            $request->validate([
                'jawaban_studi_kasus' => 'required|string|min:2',
            ], [
                'jawaban_studi_kasus.required' => 'Tuliskan jawaban studi kasus atau unggah file lampiran penyelesaian Anda.',
            ]);
        }

        $fileLampiranPath = null;
        $namaFileAsli = null;
        $tipeFile = null;

        if ($hasFile) {
            $file = $request->file('file_lampiran');
            if (!$file->isValid()) {
                return back()->withErrors(['file_lampiran' => 'File lampiran gagal diunggah.'])->withInput();
            }

            $allowedExts = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'zip', 'html', 'css', 'js', 'php'];
            $ext = strtolower($file->getClientOriginalExtension());
            if (!in_array($ext, $allowedExts, true)) {
                return back()->withErrors([
                    'file_lampiran' => 'Format file tidak didukung. Gunakan PDF, DOC/DOCX, JPG/PNG, ZIP, atau file kode (HTML/CSS/JS/PHP).',
                ])->withInput();
            }

            if ($file->getSize() > 10 * 1024 * 1024) {
                return back()->withErrors(['file_lampiran' => 'Ukuran file maksimal 10 MB.'])->withInput();
            }

            $uploadDir = public_path('uploads/studi_kasus');
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            $namaFileAsli = $file->getClientOriginalName();
            $safeName = 'kasus_m' . $target->id . '_u' . $user->id . '_' . time() . '_' . Str::random(5) . '.' . $ext;
            $file->move($uploadDir, $safeName);

            $fileLampiranPath = 'uploads/studi_kasus/' . $safeName;
            $tipeFile = strtoupper($ext);
        }

        $statsBefore = $user->getLearningStats();
        $wasAlreadyCompleted = $target->lock_state === 'selesai';

        // Pastikan record StudiKasus tersedia
        $studiKasus = StudiKasus::firstOrCreate(
            ['materi_id' => $target->id],
            [
                'judul_studi_kasus' => 'Studi Kasus: ' . ($target->judul_materi ?: $target->judul),
                'tipe_studi_kasus' => $target->tipe_studi_kasus ?: 'Esai singkat',
                'instruksi_soal' => $target->soal_studi_kasus ?: 'Selesaikan studi kasus materi ini.',
            ]
        );

        // Simpan ke tabel `pengumpulan_studi_kasus`
        $existingPengumpulan = PengumpulanStudiKasus::where('studi_kasus_id', $studiKasus->id)
            ->where('siswa_id', $user->id)
            ->first();

        PengumpulanStudiKasus::updateOrCreate(
            [
                'studi_kasus_id' => $studiKasus->id,
                'siswa_id' => $user->id,
            ],
            [
                'jawaban_teks' => $jawabanInput !== '' ? $jawabanInput : ($existingPengumpulan?->jawaban_teks ?? 'Lampiran file terlampir'),
                'file_lampiran' => $fileLampiranPath ?? $existingPengumpulan?->file_lampiran,
                'nama_file_asli' => $namaFileAsli ?? $existingPengumpulan?->nama_file_asli,
                'tipe_file' => $tipeFile ?? $existingPengumpulan?->tipe_file,
                'tanggal_kumpul' => now(),
                'status' => 'terkumpul',
            ]
        );

        // Pastikan log_baca_materi juga berstatus selesai
        LogBacaMateri::updateOrCreate(
            ['siswa_id' => $user->id, 'materi_id' => $target->id],
            ['status_baca' => 'selesai', 'waktu_selesai_baca' => now()]
        );

        // Sinkronkan ke `siswa_materi_progress`
        $prog = SiswaMateriProgress::firstOrNew([
            'siswa_id' => $user->id,
            'materi_id' => $target->id,
        ]);

        $prog->baca_selesai = true;
        $prog->jawaban_studi_kasus = $jawabanInput !== '' ? $jawabanInput : ('File: ' . ($namaFileAsli ?: 'Lampiran'));
        $prog->progress = 100;
        $prog->status = 'selesai';
        $prog->completed_at = now();
        $prog->save();

        $judul = $target->judul_materi ?: $target->judul;

        if (!$wasAlreadyCompleted) {
            RiwayatBelajar::create([
                'user_id' => $user->id,
                'tipe_aktivitas' => 'studi_kasus',
                'judul_aktivitas' => "Menyelesaikan Materi & Studi Kasus: {$judul}",
                'keterangan' => "Modul: {$activeModul->judul_modul} · Tipe: " . ($studiKasus->tipe_studi_kasus ?: 'Studi Kasus')
                    . ($namaFileAsli ? " · Lampiran: {$namaFileAsli}" : ''),
                'referensi_id' => $target->id,
            ]);
        }

        $statsAfter = $user->getLearningStats();
        $materiListInModul = $activeModul->materi_list;
        $nextMateri = isset($materiListInModul[$targetIndex + 1]) ? $materiListInModul[$targetIndex + 1] : null;
        $nextJudul = $nextMateri ? ($nextMateri->judul_materi ?: $nextMateri->judul) : null;

        $celebrationPayload = [
            'title' => $statsAfter['level'] > $statsBefore['level']
                ? 'Materi Selesai & Level Bertambah!'
                : 'Materi & Studi Kasus Tuntas!',
            'materi_judul' => $judul,
            'prev_level' => $statsBefore['level'],
            'new_level' => $statsAfter['level'],
            'rank_title' => $statsAfter['rank_title'],
            'progress_pct' => $statsAfter['progress_pct'],
            'unlocked_next' => $nextJudul,
            'unlocked_next_id' => $nextMateri?->id,
            'modul_id' => $activeModul->id,
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'celebration' => $celebrationPayload,
                'stats' => $statsAfter,
            ]);
        }

        return redirect()
            ->route('siswa.materi.show', $target->id)
            ->with('celebration', $celebrationPayload)
            ->with('success', 'Jawaban studi kasus berhasil dikumpulkan!');
    }
}
