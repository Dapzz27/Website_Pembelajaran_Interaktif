<x-layouts.siswa :title="$activeModul ? ('Modul: ' . $activeModul->judul_modul) : 'Modul & Materi Pembelajaran'">

    @php
        $user = auth()->user();
        $stats = $stats ?? $user->getLearningStats();
        $misiPct = $stats['total_materi'] > 0 ? (int) round(($stats['materi_selesai'] / $stats['total_materi']) * 100) : 0;
    @endphp

    <div class="max-w-5xl mx-auto space-y-6">

        {{-- ALERT NOTIFIKASI --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="flex items-center justify-between gap-3 rounded-xl border border-teal-500/25 bg-teal-500/10 px-5 py-3.5 text-sm font-medium text-teal-700 dark:text-teal-300">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="show = false" class="p-1 text-teal-600 dark:text-teal-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="flex items-center justify-between gap-3 rounded-xl border border-red-500/25 bg-red-500/10 px-5 py-3.5 text-sm font-medium text-red-700 dark:text-red-300">
                <span>{{ session('error') }}</span>
                <button type="button" @click="show = false" class="p-1 text-red-600 dark:text-red-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- =====================================================================
             TAHAP 1: KATALOG MODUL PEMBELAJARAN (SAAT BELUM MEMILIH MODUL)
             ===================================================================== --}}
        @if(!$activeModul)

            {{-- HEADER KURIKULUM & PROGRES TERPADU --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 lg:p-7 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="max-w-xl">
                        <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                            Katalog Kurikulum · Level {{ $stats['level'] }}
                        </span>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            Modul Pembelajaran
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                            Pilih modul yang ingin dipelajari, lalu selesaikan sub-bab materi dan studi kasus secara berurutan.
                        </p>
                    </div>

                    {{-- Ringkasan Ketuntasan Keseluruhan --}}
                    <div class="lg:w-80 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-4 flex-shrink-0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-slate-400">Progres Materi</span>
                            <span class="text-xs font-semibold text-teal-600 dark:text-teal-400">{{ $stats['materi_selesai'] }} / {{ $stats['total_materi'] }} Selesai</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-teal-600 transition-all duration-700"
                                style="width: {{ $misiPct }}%"></div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-800 grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-800 text-center">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Modul</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white mt-0.5">{{ $stats['modul_selesai'] ?? 0 }}/{{ $stats['total_modul'] ?? $modulList->count() }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Studi Kasus</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white mt-0.5">{{ $stats['studi_kasus_selesai'] }}/{{ $stats['total_studi_kasus'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Tuntas</p>
                                <p class="text-sm font-semibold text-teal-600 dark:text-teal-400 mt-0.5">{{ $misiPct }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DAFTAR KATALOG MODUL --}}
            <div class="space-y-4">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                        Pilih Modul Pembelajaran
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                        Klik salah satu modul di bawah ini untuk membuka daftar urutan sub-bab materi di dalamnya.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    @forelse($modulList as $mod)
                        @php
                            $modPct = (int) $mod->progress_pct;
                            $modDone = (bool) $mod->is_completed;
                        @endphp

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-5 sm:p-6 shadow-sm hover:border-teal-500/40 transition-colors">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                                {{-- Info Modul Kiri --}}
                                <div class="flex items-start gap-4 min-w-0 flex-1">
                                    <div class="w-11 h-11 rounded-xl {{ $modDone ? 'bg-teal-500/15 text-teal-600 dark:text-teal-400 border-teal-500/30' : 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20' }} border font-bold text-sm flex items-center justify-center flex-shrink-0 mt-0.5">
                                        @if($modDone)
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        @else
                                            {{ $mod->urutan_modul }}
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('siswa.materi', ['modul_id' => $mod->id]) }}" class="group inline-block">
                                                <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">
                                                    {{ $mod->judul_modul }}
                                                </h3>
                                            </a>

                                            @if(!empty($mod->kategori_atau_kelas))
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                                                    {{ $mod->kategori_atau_kelas }}
                                                </span>
                                            @endif

                                            @if($modDone)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-700 dark:text-teal-300 border border-teal-500/20">
                                                    Selesai
                                                </span>
                                            @endif
                                        </div>

                                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">
                                            {{ $mod->deskripsi_singkat ?: 'Pelajari sub-bab materi dan selesaikan latihan studi kasus pada modul ini.' }}
                                        </p>

                                        {{-- Badge Jumlah Materi & Studi Kasus --}}
                                        <div class="flex flex-wrap items-center gap-2.5 mt-3.5">
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                {{ $mod->jumlah_materi }} Sub-Bab Materi
                                            </span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                {{ $mod->jumlah_studi_kasus }} Studi Kasus
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Indikator Progres Modul & Tombol Aksi Kanan --}}
                                <div class="lg:w-72 flex flex-col sm:flex-row lg:flex-col items-stretch sm:items-center lg:items-stretch justify-between gap-4 pt-4 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-800 flex-shrink-0">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                                            <span class="text-slate-400">{{ $mod->materi_selesai }} / {{ $mod->jumlah_materi }} Materi Selesai</span>
                                            <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $modPct }}%</span>
                                        </div>
                                        <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                            <div class="h-full rounded-full bg-teal-600 transition-all duration-500"
                                                style="width: {{ $modPct }}%"></div>
                                        </div>
                                    </div>

                                    <a href="{{ route('siswa.materi', ['modul_id' => $mod->id]) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl {{ $modDone ? 'border border-teal-500/30 bg-teal-500/10 hover:bg-teal-500/20 text-teal-700 dark:text-teal-300' : 'bg-teal-600 hover:bg-teal-500 text-white shadow-sm' }} px-5 py-2.5 text-xs font-semibold transition whitespace-nowrap">
                                        <span>{{ $modDone ? 'Tinjau Modul' : ($modPct > 0 ? 'Lanjutkan Modul' : 'Mulai Pelajari') }}</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-12 text-center">
                            <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum ada modul pembelajaran</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Modul yang ditambahkan oleh Guru akan tampil di halaman ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        @else

        {{-- =====================================================================
             TAHAP 2: URUTAN SUB-BAB MATERI DI DALAM MODUL YANG DIPILIH (DENGAN GEMBOK)
             ===================================================================== --}}

            {{-- TOMBOL KEMBALI KE KATALOG MODUL --}}
            <div>
                <a href="{{ route('siswa.materi') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>← Kembali ke Katalog Modul</span>
                </a>
            </div>

            {{-- HEADER MODUL AKTIF & PROGRES MODUL --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 lg:p-7 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="max-w-xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                                Modul #{{ $activeModul->urutan_modul }}
                            </span>
                            @if(!empty($activeModul->kategori_atau_kelas))
                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                <span class="text-xs font-medium text-slate-400">{{ $activeModul->kategori_atau_kelas }}</span>
                            @endif
                        </div>

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $activeModul->judul_modul }}
                        </h1>

                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                            {{ $activeModul->deskripsi_singkat ?: 'Pelajari setiap sub-bab secara berurutan dan selesaikan studi kasus untuk membuka sub-bab berikutnya.' }}
                        </p>
                    </div>

                    {{-- Ringkasan Ketuntasan Modul Ini --}}
                    <div class="lg:w-80 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-4 flex-shrink-0">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-slate-400">Progres Modul Ini</span>
                            <span class="text-xs font-semibold text-teal-600 dark:text-teal-400">{{ $activeModul->materi_selesai }} / {{ $activeModul->jumlah_materi }} Materi Selesai</span>
                        </div>
                        <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-teal-600 transition-all duration-700"
                                style="width: {{ $activeModul->progress_pct }}%"></div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-800 grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-800 text-center">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Sub-Bab</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white mt-0.5">{{ $activeModul->jumlah_materi }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Studi Kasus</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-white mt-0.5">{{ $activeModul->studi_kasus_selesai }}/{{ $activeModul->jumlah_studi_kasus }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-400">Ketuntasan</p>
                                <p class="text-sm font-semibold text-teal-600 dark:text-teal-400 mt-0.5">{{ $activeModul->progress_pct }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DAFTAR URUTAN SUB-BAB MATERI DI DALAM MODUL --}}
            <div class="space-y-4">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                        Daftar Sub-Bab Materi
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                        Sub-bab pertama terbuka otomatis. Selesaikan bacaan dan studi kasus untuk membuka gembok sub-bab berikutnya.
                    </p>
                </div>

                <div class="space-y-3.5">
                    @forelse($activeModul->materi_list as $index => $item)
                        @php
                            $judul = $item->judul_materi ?: $item->judul;
                            $state = $item->lock_state; // selesai | terbuka | terkunci
                            $hasVideo = (bool) $item->has_video
                                || !empty($item->video_url)
                                || $item->bagianMateri->contains(fn ($b) => $b->tipe_media === 'video' && !empty($b->url_video));
                            $tipeKasus = $item->studiKasus?->tipe_studi_kasus ?? ($item->tipe_studi_kasus ?: '');
                            $jumlahBagian = max(1, $item->bagianMateri->count());
                        @endphp

                        <div class="relative flex items-stretch gap-3 sm:gap-4">
                            {{-- Indikator Timeline Kiri --}}
                            <div class="flex flex-col items-center flex-shrink-0">
                                @if($state === 'selesai')
                                    <div class="w-10 h-10 rounded-xl bg-teal-500/15 text-teal-600 dark:text-teal-400 border border-teal-500/25 flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                @elseif($state === 'terbuka')
                                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 font-bold text-sm flex items-center justify-center">
                                        {{ $index + 1 }}
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                @endif
                                @if(!$loop->last)
                                    <div class="w-px flex-1 bg-slate-200 dark:bg-slate-800 mt-2"></div>
                                @endif
                            </div>

                            {{-- Kartu Sub-Bab Materi --}}
                            <div class="flex-1 min-w-0 rounded-2xl border {{ $state === 'terkunci' ? 'border-slate-200/60 dark:border-slate-800/80 bg-slate-50/70 dark:bg-[#08142c]/50 opacity-75' : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] hover:border-teal-500/40' }} p-5 transition-colors">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2 mb-1">
                                            <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                                                {{ $item->bab ?: 'Bab 1' }} · {{ $item->sub_bab ?: 'Sub 1' }}
                                            </span>
                                            <span class="text-slate-300 dark:text-slate-700">•</span>
                                            <span class="text-xs font-medium text-slate-400">
                                                {{ $jumlahBagian }} Bagian Bacaan
                                            </span>
                                            @if($item->completed_at)
                                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                                <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                                                    Selesai {{ $item->completed_at }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($state !== 'terkunci')
                                            <a href="{{ route('siswa.materi.show', $item->id) }}" class="group inline-block">
                                                <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">
                                                    {{ $judul }}
                                                </h3>
                                            </a>
                                        @else
                                            <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-slate-200">
                                                {{ $judul }}
                                            </h3>
                                        @endif

                                        {{-- Deretan Badge Informasi --}}
                                        <div class="flex flex-wrap items-center gap-2 mt-3">
                                            @if($state === 'selesai')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-700 dark:text-teal-300 border border-teal-500/20">
                                                    Selesai
                                                </span>
                                            @elseif($state === 'terbuka')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                                                    {{ $item->baca_selesai ? 'Sudah Dibaca · Menunggu Studi Kasus' : 'Terbuka' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                                    Selesaikan “{{ $item->prev_judul }}” terlebih dahulu
                                                </span>
                                            @endif

                                            @if(!empty($tipeKasus))
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                    Studi kasus: {{ $tipeKasus }}
                                                </span>
                                            @endif

                                            @if($hasVideo)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                    Video
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tombol Navigasi ke Halaman Detail Penuh --}}
                                    <div class="flex-shrink-0">
                                        @if($state === 'terkunci')
                                            <span class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900 px-4 py-2.5 text-xs font-medium text-slate-400 cursor-not-allowed">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                <span>Terkunci</span>
                                            </span>
                                        @elseif($state === 'selesai')
                                            <a href="{{ route('siswa.materi.show', $item->id) }}"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl border border-teal-500/30 bg-teal-500/10 hover:bg-teal-500/20 px-4 py-2.5 text-xs font-semibold text-teal-700 dark:text-teal-300 transition">
                                                <span>Buka Materi</span>
                                            </a>
                                        @else
                                            <a href="{{ route('siswa.materi.show', $item->id) }}"
                                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition">
                                                <span>{{ $item->baca_selesai ? 'Lanjutkan Studi Kasus' : 'Pelajari Materi' }}</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-12 text-center">
                            <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum ada sub-bab materi di modul ini</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Guru belum menambahkan sub-bab materi pada modul ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        @endif

    </div>

</x-layouts.siswa>
