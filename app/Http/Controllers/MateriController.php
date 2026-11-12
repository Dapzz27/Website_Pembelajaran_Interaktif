<?php

namespace App\Http\Controllers;

use App\Models\BagianMateri;
use App\Models\LogBacaMateri;
use App\Models\Materi;
use App\Models\Modul;
use App\Models\PengumpulanStudiKasus;
use App\Models\SiswaMateriProgress;
use App\Models\StudiKasus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MateriController extends Controller
{
    /**
     * Pastikan tidak ada materi tanpa modul_id (Migrasi Data Aman),
     * dan pastikan urutan_modul serta urutan materi di setiap modul selalu berurutan (1, 2, 3, ...).
     */
    protected function ensureIntegrityAndNormalize(?int $onlyModulId = null): void
    {
        // Jika ada materi yatim (modul_id null), hubungkan ke modul pertama atau buat modul awal
        if (Materi::whereNull('modul_id')->exists()) {
            $firstModul = Modul::orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->first();
            if (!$firstModul) {
                $firstModul = Modul::create([
                    'judul_modul' => 'Pemrograman Web Dasar',
                    'kategori_atau_kelas' => 'Kelas X / XI RPL',
                    'deskripsi_singkat' => 'Fondasi pengembangan antarmuka web modern mencakup struktur dokumen HTML, penggunaan tag dan atribut, elemen semantik, serta studi kasus pembuatan halaman web.',
                    'thumbnail_atau_ikon' => 'code',
                    'urutan_modul' => 1,
                    'created_by' => Auth::id(),
                ]);
            }
            Materi::whereNull('modul_id')->update(['modul_id' => $firstModul->id]);
        }

        // Normalisasi urutan_modul (1..N)
        $moduls = Modul::orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->get();
        foreach ($moduls as $mIdx => $mod) {
            $expectedModUrutan = $mIdx + 1;
            if ((int) $mod->urutan_modul !== $expectedModUrutan) {
                $mod->urutan_modul = $expectedModUrutan;
                $mod->saveQuietly();
            }
        }

        // Normalisasi urutan materi di dalam setiap modul (1..N per modul)
        $targetModulIds = $onlyModulId
            ? [$onlyModulId]
            : $moduls->pluck('id')->toArray();

        foreach ($targetModulIds as $mId) {
            $items = Materi::where('modul_id', $mId)
                ->orderBy('urutan', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($items as $idx => $item) {
                $expected = $idx + 1;
                if ((int) $item->urutan !== $expected) {
                    $item->urutan = $expected;
                    $item->saveQuietly();
                }
            }
        }
    }

    public function index(Request $request)
    {
        $this->ensureIntegrityAndNormalize();

        $modulList = Modul::with([
            'materi.guru',
            'materi.bagianMateri',
            'materi.studiKasus.pengumpulan.siswa',
        ])
            ->orderBy('urutan_modul', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Hitung statistik tiap modul
        $modulList->each(function ($mod) {
            $mod->jumlah_materi = $mod->materi->count();
            $mod->jumlah_studi_kasus = $mod->materi->filter(function ($m) {
                return $m->studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus));
            })->count();
            $mod->jumlah_video = $mod->materi->filter(function ($m) {
                return (bool) $m->has_video
                    || !empty($m->video_url)
                    || $m->bagianMateri->contains(fn ($b) => $b->tipe_media === 'video' && !empty($b->url_video));
            })->count();
        });

        $totalModul = $modulList->count();
        $totalMateriAll = $modulList->sum('jumlah_materi');
        $totalStudiKasusAll = $modulList->sum('jumlah_studi_kasus');

        // Deteksi modul yang sedang dibuka (Tahap 2)
        $selectedModulId = $request->filled('modul_id') ? (int) $request->query('modul_id') : null;

        // Jika Guru datang dari tombol "Edit Materi Ini" (?edit=ID) tanpa modul_id, arahkan otomatis ke modul terkait
        if (!$selectedModulId && $request->filled('edit')) {
            $editMateri = Materi::find((int) $request->query('edit'));
            if ($editMateri && $editMateri->modul_id) {
                $selectedModulId = (int) $editMateri->modul_id;
            }
        }

        $activeModul = $selectedModulId
            ? $modulList->firstWhere('id', $selectedModulId)
            : null;

        if ($activeModul) {
            $materi = $activeModul->materi->values();
            $totalMateri = $materi->count();
            $totalStudiKasus = $activeModul->jumlah_studi_kasus;
        } else {
            $materi = collect();
            $totalMateri = $totalMateriAll;
            $totalStudiKasus = $totalStudiKasusAll;
        }

        return view('admin_guru.materi', compact(
            'modulList',
            'activeModul',
            'materi',
            'totalModul',
            'totalMateriAll',
            'totalStudiKasusAll',
            'totalMateri',
            'totalStudiKasus'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD MODUL PEMBELAJARAN (TAHAP 1)
    |--------------------------------------------------------------------------
    */

    public function storeModul(Request $request)
    {
        $validated = $request->validate([
            'judul_modul' => 'required|string|max:255',
            'kategori_atau_kelas' => 'nullable|string|max:150',
            'deskripsi_singkat' => 'nullable|string|max:2000',
            'urutan_modul' => 'nullable|integer|min:1',
        ], [
            'judul_modul.required' => 'Judul modul wajib diisi.',
        ]);

        $maxUrutan = (int) Modul::max('urutan_modul');
        $targetUrutan = $request->filled('urutan_modul')
            ? max(1, min((int) $validated['urutan_modul'], $maxUrutan + 1))
            : ($maxUrutan + 1);

        Modul::where('urutan_modul', '>=', $targetUrutan)->increment('urutan_modul');

        Modul::create([
            'judul_modul' => trim($validated['judul_modul']),
            'kategori_atau_kelas' => !empty($validated['kategori_atau_kelas']) ? trim($validated['kategori_atau_kelas']) : null,
            'deskripsi_singkat' => !empty($validated['deskripsi_singkat']) ? trim($validated['deskripsi_singkat']) : null,
            'urutan_modul' => $targetUrutan,
            'created_by' => Auth::id(),
        ]);

        $this->ensureIntegrityAndNormalize();

        return redirect()->route('guru.materi')->with('success', 'Modul pembelajaran berhasil ditambahkan!');
    }

    public function updateModul(Request $request, $id)
    {
        $validated = $request->validate([
            'judul_modul' => 'required|string|max:255',
            'kategori_atau_kelas' => 'nullable|string|max:150',
            'deskripsi_singkat' => 'nullable|string|max:2000',
            'urutan_modul' => 'nullable|integer|min:1',
        ], [
            'judul_modul.required' => 'Judul modul wajib diisi.',
        ]);

        $modul = Modul::findOrFail($id);

        $modul->update([
            'judul_modul' => trim($validated['judul_modul']),
            'kategori_atau_kelas' => !empty($validated['kategori_atau_kelas']) ? trim($validated['kategori_atau_kelas']) : null,
            'deskripsi_singkat' => !empty($validated['deskripsi_singkat']) ? trim($validated['deskripsi_singkat']) : null,
        ]);

        if ($request->filled('urutan_modul')) {
            $items = Modul::orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->get()->values();
            $currentIndex = $items->search(fn ($m) => (int) $m->id === (int) $modul->id);
            $newIndex = max(0, min((int) $validated['urutan_modul'] - 1, $items->count() - 1));

            if ($currentIndex !== false && $currentIndex !== $newIndex) {
                $moved = $items->pull($currentIndex);
                $items = $items->values();
                $items->splice($newIndex, 0, [$moved]);

                foreach ($items->values() as $idx => $item) {
                    $item->urutan_modul = $idx + 1;
                    $item->saveQuietly();
                }
            } else {
                $this->ensureIntegrityAndNormalize();
            }
        } else {
            $this->ensureIntegrityAndNormalize();
        }

        return back()->with('success', 'Informasi modul pembelajaran berhasil diperbarui!');
    }

    public function updateUrutanModul(Request $request, $id)
    {
        $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $items = Modul::orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->get()->values();
        $currentIndex = $items->search(fn ($m) => (int) $m->id === (int) $id);

        if ($currentIndex === false) {
            return back()->with('error', 'Modul tidak ditemukan.');
        }

        $direction = $request->input('direction');
        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($swapIndex >= 0 && $swapIndex < $items->count()) {
            $temp = $items[$currentIndex];
            $items[$currentIndex] = $items[$swapIndex];
            $items[$swapIndex] = $temp;

            foreach ($items as $idx => $item) {
                $item->urutan_modul = $idx + 1;
                $item->saveQuietly();
            }
        }

        return back()->with('success', 'Urutan modul pembelajaran berhasil diperbarui!');
    }

    public function destroyModul($id)
    {
        $modul = Modul::findOrFail($id);

        $materiIds = Materi::where('modul_id', $modul->id)->pluck('id');
        if ($materiIds->isNotEmpty()) {
            $studiKasusIds = StudiKasus::whereIn('materi_id', $materiIds)->pluck('id');
            if ($studiKasusIds->isNotEmpty()) {
                PengumpulanStudiKasus::whereIn('studi_kasus_id', $studiKasusIds)->delete();
                StudiKasus::whereIn('id', $studiKasusIds)->delete();
            }
            BagianMateri::whereIn('materi_id', $materiIds)->delete();
            LogBacaMateri::whereIn('materi_id', $materiIds)->delete();
            SiswaMateriProgress::whereIn('materi_id', $materiIds)->delete();
            Materi::whereIn('id', $materiIds)->delete();
        }

        $modul->delete();
        $this->ensureIntegrityAndNormalize();

        return redirect()->route('guru.materi')->with('success', 'Modul beserta seluruh materi di dalamnya berhasil dihapus!');
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD & REVIEW MATERI DI DALAM MODUL (TAHAP 2)
    |--------------------------------------------------------------------------
    */

    /**
     * Halaman Review / Detail Materi Penuh untuk Guru (100% identik dengan tampilan baca Siswa).
     */
    public function show($id)
    {
        $this->ensureIntegrityAndNormalize();

        $materi = Materi::with([
            'modul',
            'guru',
            'bagianMateri',
            'studiKasus.pengumpulan.siswa',
        ])->find($id);

        if (!$materi) {
            return redirect()->route('guru.materi')->with('error', 'Materi tidak ditemukan.');
        }

        $materiList = Materi::with([
            'modul',
            'guru',
            'bagianMateri',
            'studiKasus.pengumpulan.siswa',
        ])
            ->where('modul_id', $materi->modul_id)
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->values();

        $targetIndex = $materiList->search(fn ($m) => (int) $m->id === (int) $materi->id);

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

        if (!$materi->studiKasus && !empty(trim((string) $materi->tipe_studi_kasus))) {
            StudiKasus::create([
                'materi_id' => $materi->id,
                'judul_studi_kasus' => 'Studi Kasus: ' . ($materi->judul_materi ?: $materi->judul),
                'tipe_studi_kasus' => $materi->tipe_studi_kasus,
                'instruksi_soal' => $materi->soal_studi_kasus ?: 'Selesaikan studi kasus berdasarkan materi di atas.',
                'opsi_a' => $materi->opsi_a,
                'opsi_b' => $materi->opsi_b,
                'opsi_c' => $materi->opsi_c,
                'opsi_d' => $materi->opsi_d,
                'kunci_jawaban' => $materi->kunci_studi_kasus,
            ]);
            $materi->load('studiKasus.pengumpulan.siswa');
        }

        $studiKasus = $materi->studiKasus;
        $prevMateri = ($targetIndex !== false && $targetIndex > 0) ? $materiList[$targetIndex - 1] : null;
        $nextMateri = ($targetIndex !== false && isset($materiList[$targetIndex + 1])) ? $materiList[$targetIndex + 1] : null;

        return view('admin_guru.materi_detail', compact(
            'materi',
            'materiList',
            'studiKasus',
            'prevMateri',
            'nextMateri'
        ));
    }

    /**
     * Simpan bagian-bagian materi (`bagian_materi`) dan `studi_kasus`.
     */
    protected function syncBlocksAndStudiKasus(Request $request, Materi $materi): void
    {
        $rawBlocks = $request->input('blocks', []);
        if (is_string($rawBlocks)) {
            $rawBlocks = json_decode($rawBlocks, true) ?? [];
        }

        if (empty($rawBlocks) && ($request->filled('konten_materi') || $request->filled('video_url'))) {
            $hasVid = $request->boolean('has_video') || $request->filled('video_url');
            $rawBlocks = [
                [
                    'tipe_layout' => $hasVid ? 'media_kiri' : 'teks_penuh',
                    'sub_judul' => $materi->judul_materi,
                    'isi_teks' => $request->input('konten_materi', ''),
                    'tipe_media' => $hasVid ? 'video' : 'none',
                    'url_video' => $request->input('video_url', ''),
                ],
            ];
        }

        $uploadedImages = $request->file('block_images', []);
        $uploadDir = public_path('uploads/materi');
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0775, true);
        }

        BagianMateri::where('materi_id', $materi->id)->delete();

        $summaryTexts = [];
        $firstVideoUrl = null;
        $hasAnyVideo = false;

        foreach (array_values($rawBlocks) as $idx => $blk) {
            $layout = $blk['tipe_layout'] ?? 'teks_penuh';
            if (!in_array($layout, ['teks_penuh', 'media_kiri', 'media_kanan', 'media_penuh'], true)) {
                $layout = 'teks_penuh';
            }

            $tipeMedia = $blk['tipe_media'] ?? 'none';
            if ($layout === 'teks_penuh') {
                $tipeMedia = 'none';
            } elseif (!in_array($tipeMedia, ['gambar', 'video', 'none'], true)) {
                $tipeMedia = 'none';
            }

            $fileGambarPath = $blk['existing_gambar'] ?? null;
            if (isset($uploadedImages[$idx]) && $uploadedImages[$idx] && $uploadedImages[$idx]->isValid()) {
                $ext = strtolower($uploadedImages[$idx]->getClientOriginalExtension() ?: 'png');
                $filename = 'materi_' . $materi->id . '_blok_' . ($idx + 1) . '_' . time() . '_' . Str::random(5) . '.' . $ext;
                $uploadedImages[$idx]->move($uploadDir, $filename);
                $fileGambarPath = 'uploads/materi/' . $filename;
                if ($layout !== 'teks_penuh') {
                    $tipeMedia = 'gambar';
                }
            }

            $urlVideo = isset($blk['url_video']) ? trim((string) $blk['url_video']) : null;
            if ($tipeMedia === 'video' && !empty($urlVideo)) {
                $hasAnyVideo = true;
                if ($firstVideoUrl === null) {
                    $firstVideoUrl = $urlVideo;
                }
            }

            $subJudul = isset($blk['sub_judul']) ? trim((string) $blk['sub_judul']) : null;
            $isiTeks = isset($blk['isi_teks']) ? trim((string) $blk['isi_teks']) : null;

            if (!empty($isiTeks)) {
                $summaryTexts[] = $isiTeks;
            } elseif (!empty($subJudul)) {
                $summaryTexts[] = $subJudul;
            }

            BagianMateri::create([
                'materi_id' => $materi->id,
                'tipe_layout' => $layout,
                'sub_judul' => $subJudul ?: null,
                'isi_teks' => $isiTeks ?: null,
                'tipe_media' => $tipeMedia,
                'file_gambar' => $tipeMedia === 'gambar' ? $fileGambarPath : null,
                'url_video' => $tipeMedia === 'video' ? $urlVideo : null,
                'urutan_blok' => $idx + 1,
            ]);
        }

        $combinedText = !empty($summaryTexts)
            ? implode("\n\n", $summaryTexts)
            : ($request->input('konten_materi') ?? $materi->konten_materi);

        if (!$hasAnyVideo && ($request->boolean('has_video') || $request->filled('video_url'))) {
            $hasAnyVideo = true;
            $firstVideoUrl = $request->input('video_url');
        }

        $tipeKasus = $request->input('tipe_studi_kasus');
        $tipeKasus = !empty($tipeKasus) ? trim((string) $tipeKasus) : null;
        $judulKasus = $request->input('judul_studi_kasus')
            ? trim((string) $request->input('judul_studi_kasus'))
            : ('Studi Kasus: ' . $materi->judul_materi);
        $instruksiSoal = $request->input('instruksi_soal', $request->input('soal_studi_kasus'));
        $instruksiSoal = $instruksiSoal !== null ? trim((string) $instruksiSoal) : null;

        $materi->update([
            'deskripsi' => $combinedText,
            'konten_materi' => $combinedText,
            'has_video' => $hasAnyVideo,
            'video_url' => $hasAnyVideo ? $firstVideoUrl : null,
            'tipe_studi_kasus' => $tipeKasus,
            'soal_studi_kasus' => $tipeKasus ? $instruksiSoal : null,
            'opsi_a' => $request->input('opsi_a'),
            'opsi_b' => $request->input('opsi_b'),
            'opsi_c' => $request->input('opsi_c'),
            'opsi_d' => $request->input('opsi_d'),
        ]);

        if ($tipeKasus) {
            StudiKasus::updateOrCreate(
                ['materi_id' => $materi->id],
                [
                    'judul_studi_kasus' => $judulKasus,
                    'tipe_studi_kasus' => $tipeKasus,
                    'instruksi_soal' => $instruksiSoal,
                    'opsi_a' => $request->input('opsi_a'),
                    'opsi_b' => $request->input('opsi_b'),
                    'opsi_c' => $request->input('opsi_c'),
                    'opsi_d' => $request->input('opsi_d'),
                    'kunci_jawaban' => $request->input('kunci_jawaban'),
                ]
            );
        } else {
            StudiKasus::where('materi_id', $materi->id)->delete();
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'modul_id' => 'nullable|integer|exists:modul,id',
            'judul_materi' => 'required|string|max:255',
            'bab' => 'required|string|max:100',
            'sub_bab' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:1',
            'block_images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ], [
            'judul_materi.required' => 'Judul materi wajib diisi.',
            'bab.required' => 'Bab wajib diisi (contoh: Bab 1).',
            'sub_bab.required' => 'Sub bab wajib diisi (contoh: Sub 1).',
        ]);

        $modulId = !empty($validated['modul_id']) ? (int) $validated['modul_id'] : null;
        if (!$modulId) {
            $firstModul = Modul::orderBy('urutan_modul', 'asc')->orderBy('id', 'asc')->first();
            if (!$firstModul) {
                $firstModul = Modul::create([
                    'judul_modul' => 'Pemrograman Web Dasar',
                    'kategori_atau_kelas' => 'Kelas X / XI RPL',
                    'deskripsi_singkat' => 'Fondasi pengembangan antarmuka web modern.',
                    'urutan_modul' => 1,
                    'created_by' => Auth::id(),
                ]);
            }
            $modulId = (int) $firstModul->id;
        }

        $maxUrutan = (int) Materi::where('modul_id', $modulId)->max('urutan');
        $targetUrutan = $request->filled('urutan')
            ? max(1, min((int) $validated['urutan'], $maxUrutan + 1))
            : ($maxUrutan + 1);

        Materi::where('modul_id', $modulId)
            ->where('urutan', '>=', $targetUrutan)
            ->increment('urutan');

        $materi = Materi::create([
            'modul_id' => $modulId,
            'guru_id' => Auth::id(),
            'created_by' => Auth::id(),
            'judul' => trim($validated['judul_materi']),
            'judul_materi' => trim($validated['judul_materi']),
            'bab' => trim($validated['bab']),
            'sub_bab' => trim($validated['sub_bab']),
            'urutan' => $targetUrutan,
        ]);

        $this->syncBlocksAndStudiKasus($request, $materi);
        $this->ensureIntegrityAndNormalize($modulId);

        return redirect()
            ->route('guru.materi', ['modul_id' => $modulId])
            ->with('success', 'Materi, bagian konten, dan studi kasus berhasil disimpan!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'modul_id' => 'nullable|integer|exists:modul,id',
            'judul_materi' => 'required|string|max:255',
            'bab' => 'required|string|max:100',
            'sub_bab' => 'required|string|max:100',
            'urutan' => 'nullable|integer|min:1',
            'block_images.*' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
        ], [
            'judul_materi.required' => 'Judul materi wajib diisi.',
            'bab.required' => 'Bab wajib diisi (contoh: Bab 1).',
            'sub_bab.required' => 'Sub bab wajib diisi (contoh: Sub 1).',
        ]);

        $materi = Materi::findOrFail($id);
        $modulId = !empty($validated['modul_id']) ? (int) $validated['modul_id'] : (int) $materi->modul_id;

        $materi->update([
            'modul_id' => $modulId,
            'judul' => trim($validated['judul_materi']),
            'judul_materi' => trim($validated['judul_materi']),
            'bab' => trim($validated['bab']),
            'sub_bab' => trim($validated['sub_bab']),
        ]);

        $this->syncBlocksAndStudiKasus($request, $materi);

        if ($request->filled('urutan')) {
            $items = Materi::where('modul_id', $modulId)
                ->orderBy('urutan', 'asc')
                ->orderBy('id', 'asc')
                ->get()
                ->values();
            $currentIndex = $items->search(fn ($m) => (int) $m->id === (int) $materi->id);
            $newIndex = max(0, min((int) $validated['urutan'] - 1, $items->count() - 1));

            if ($currentIndex !== false && $currentIndex !== $newIndex) {
                $moved = $items->pull($currentIndex);
                $items = $items->values();
                $items->splice($newIndex, 0, [$moved]);

                foreach ($items->values() as $idx => $item) {
                    $item->urutan = $idx + 1;
                    $item->saveQuietly();
                }
            } else {
                $this->ensureIntegrityAndNormalize($modulId);
            }
        } else {
            $this->ensureIntegrityAndNormalize($modulId);
        }

        return redirect()
            ->route('guru.materi', ['modul_id' => $modulId])
            ->with('success', 'Materi dan susunan bagian berhasil diperbarui!');
    }

    public function updateUrutan(Request $request, $id)
    {
        $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        $materi = Materi::findOrFail($id);
        $modulId = (int) $materi->modul_id;

        $items = Materi::where('modul_id', $modulId)
            ->orderBy('urutan', 'asc')
            ->orderBy('id', 'asc')
            ->get()
            ->values();

        $currentIndex = $items->search(fn ($m) => (int) $m->id === (int) $id);

        if ($currentIndex === false) {
            return back()->with('error', 'Materi tidak ditemukan.');
        }

        $direction = $request->input('direction');
        $swapIndex = $direction === 'up' ? $currentIndex - 1 : $currentIndex + 1;

        if ($swapIndex >= 0 && $swapIndex < $items->count()) {
            $temp = $items[$currentIndex];
            $items[$currentIndex] = $items[$swapIndex];
            $items[$swapIndex] = $temp;

            foreach ($items as $idx => $item) {
                $item->urutan = $idx + 1;
                $item->saveQuietly();
            }
        }

        return redirect()
            ->route('guru.materi', ['modul_id' => $modulId])
            ->with('success', 'Urutan materi berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $materi = Materi::findOrFail($id);
        $modulId = (int) $materi->modul_id;

        $studiKasusIds = StudiKasus::where('materi_id', $materi->id)->pluck('id');
        if ($studiKasusIds->isNotEmpty()) {
            PengumpulanStudiKasus::whereIn('studi_kasus_id', $studiKasusIds)->delete();
            StudiKasus::whereIn('id', $studiKasusIds)->delete();
        }

        BagianMateri::where('materi_id', $materi->id)->delete();
        LogBacaMateri::where('materi_id', $materi->id)->delete();
        SiswaMateriProgress::where('materi_id', $materi->id)->delete();

        $materi->delete();

        $this->ensureIntegrityAndNormalize($modulId);

        return redirect()
            ->route('guru.materi', ['modul_id' => $modulId])
            ->with('success', 'Materi beserta seluruh bagiannya berhasil dihapus!');
    }
}
