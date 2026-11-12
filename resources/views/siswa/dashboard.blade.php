<x-layouts.siswa title="Dashboard Siswa">

    @php
        $user = auth()->user();
        $stats = $stats ?? $user->getLearningStats();
        $moduls = $modulList ?? collect();
        $currentModul = $activeModul ?? $moduls->first();
        $currentQuest = $activeQuest ?? ($currentModul ? $currentModul->materi_list->firstWhere('lock_state', 'terbuka') : null);
    @endphp

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- 1. UNIFIED HEADER & METRIK TERINTEGRASI --}}
        <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] shadow-sm overflow-hidden">

            <div class="p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    {{-- Kiri: Sapaan & Deskripsi --}}
                    <div class="space-y-1.5 max-w-2xl">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
                            Level {{ $stats['level'] }} · {{ $stats['rank_title'] }}
                        </span>

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white pt-1">
                            Selamat Datang, {{ explode(' ', $user->name)[0] }}
                        </h1>

                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            @if($stats['level'] === 0 && $stats['progress_pct'] === 0)
                                Progres belajar Anda dimulai dari Level 0. Pilih modul pembelajaran di bawah ini dan tuntaskan sub-bab materi beserta studi kasusnya.
                            @else
                                Pantau progres kurikulum Anda dan lanjutkan modul pembelajaran untuk menyelesaikan sub-bab materi berikutnya.
                            @endif
                        </p>
                    </div>

                    {{-- Kanan: Indikator Level & Progress Bar Solid Biru Tosca --}}
                    <div class="w-full lg:w-96 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 p-4 sm:p-5 flex-shrink-0">
                        <div class="flex items-center justify-between gap-4 mb-2.5">
                            <div>
                                <p class="text-xs font-medium text-slate-400">Progres Kurikulum</p>
                                <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mt-0.5">
                                    {{ $stats['progress_pct'] }}% Selesai
                                </p>
                            </div>
                            <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                                Target Level {{ $stats['level'] + 1 }}
                            </span>
                        </div>

                        <div class="h-2.5 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-teal-600 transition-all duration-700 ease-out"
                                style="width: {{ $stats['progress_pct'] }}%"></div>
                        </div>

                        <div class="mt-2.5 flex items-center justify-between text-xs font-medium text-slate-400">
                            <span>Progres Level: {{ $stats['next_level_pct'] }}%</span>
                            <span>{{ $stats['modul_selesai'] ?? 0 }} / {{ $stats['total_modul'] ?? $moduls->count() }} Modul Tuntas</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Strip Metrik Terintegrasi (divide-x) --}}
            <div class="border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-[#061024]/70 px-6 py-4 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-0 lg:divide-x lg:divide-slate-200 dark:lg:divide-slate-800">

                    <div class="lg:pr-6">
                        <p class="text-xs font-medium text-slate-400">Materi Selesai</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $stats['materi_selesai'] }} <span class="text-sm font-normal text-slate-400">/ {{ $stats['total_materi'] }}</span>
                        </p>
                    </div>

                    <div class="lg:px-6">
                        <p class="text-xs font-medium text-slate-400">Studi Kasus</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $stats['studi_kasus_selesai'] }} <span class="text-sm font-normal text-slate-400">/ {{ $stats['total_studi_kasus'] }}</span>
                        </p>
                    </div>

                    <div class="lg:px-6">
                        <p class="text-xs font-medium text-slate-400">Quiz Diselesaikan</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $stats['quiz_selesai'] }} <span class="text-sm font-normal text-slate-400">/ {{ $stats['total_quiz'] }}</span>
                        </p>
                    </div>

                    <div class="lg:pl-6">
                        <p class="text-xs font-medium text-slate-400">Rata-rata Skor Quiz</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $stats['rata_skor_quiz'] }} <span class="text-sm font-normal text-slate-400">poin</span>
                        </p>
                    </div>

                </div>
            </div>
        </section>

        {{-- 2. DAFTAR MODUL PEMBELAJARAN & RINGKASAN AKTIVITAS (ASYMMETRICAL SPLIT 8/12 + 4/12) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- KOLOM UTAMA (8/12): DAFTAR MODUL PEMBELAJARAN --}}
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Modul Pembelajaran
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Pilih modul untuk melihat daftar urutan sub-bab materi dan mengerjakan studi kasus.
                        </p>
                    </div>
                    <a href="{{ route('siswa.materi') }}"
                        class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline flex-shrink-0">
                        Buka Katalog Modul →
                    </a>
                </div>

                <div class="space-y-3.5">
                    @forelse($moduls as $mod)
                        @php
                            $modPct = (int) $mod->progress_pct;
                            $modDone = (bool) $mod->is_completed;
                        @endphp

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-5 sm:p-6 shadow-sm hover:border-teal-500/40 transition-colors">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                    <div class="w-10 h-10 rounded-xl {{ $modDone ? 'bg-teal-500/15 text-teal-600 dark:text-teal-400 border-teal-500/30' : 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20' }} border font-bold text-sm flex items-center justify-center flex-shrink-0 mt-0.5">
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
                                        </div>

                                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 line-clamp-2">
                                            {{ $mod->deskripsi_singkat ?: 'Pelajari sub-bab materi secara berurutan dan selesaikan studi kasus di dalam modul ini.' }}
                                        </p>

                                        <div class="flex flex-wrap items-center gap-2 mt-3">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                {{ $mod->jumlah_materi }} Sub-Bab Materi
                                            </span>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                {{ $mod->jumlah_studi_kasus }} Studi Kasus
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-shrink-0">
                                    <a href="{{ route('siswa.materi', ['modul_id' => $mod->id]) }}"
                                        class="inline-flex items-center justify-center gap-2 rounded-xl {{ $modDone ? 'border border-teal-500/30 bg-teal-500/10 hover:bg-teal-500/20 text-teal-700 dark:text-teal-300' : 'bg-teal-600 hover:bg-teal-500 text-white shadow-sm' }} px-4 py-2.5 text-xs font-semibold transition">
                                        <span>{{ $modDone ? 'Tinjau Modul' : ($modPct > 0 ? 'Lanjutkan Modul' : 'Buka Modul') }}</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            {{-- Progress Bar Ketuntasan Siswa pada Modul Ini --}}
                            <div class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80">
                                <div class="flex items-center justify-between text-xs font-medium mb-1.5">
                                    <span class="text-slate-400">Ketuntasan Modul: {{ $mod->materi_selesai }} / {{ $mod->jumlah_materi }} Materi Selesai</span>
                                    <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $modPct }}%</span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full rounded-full bg-teal-600 transition-all duration-500"
                                        style="width: {{ $modPct }}%"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-10 text-center shadow-sm">
                            <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum ada modul pembelajaran</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Modul yang dipublikasikan oleh Guru akan tampil di halaman ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- KOLOM SAMPING (4/12): SUB-BAB AKTIF & PINTASAN EVALUASI --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Sub-Bab Materi Terbuka Saat Ini --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <p class="text-xs font-medium text-teal-600 dark:text-teal-400">Lanjutkan Belajar</p>
                        @if($currentModul)
                            <span class="text-xs font-medium text-slate-400 truncate max-w-[160px]">
                                {{ $currentModul->judul_modul }}
                            </span>
                        @endif
                    </div>

                    @if($currentQuest)
                        @php
                            $qJudul = $currentQuest->judul_materi ?: $currentQuest->judul;
                            $qPct = (int) $currentQuest->progress_pct;
                        @endphp
                        <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                            {{ $qJudul }}
                        </h3>
                        <p class="text-xs font-medium text-slate-400 mt-1">
                            {{ $currentQuest->bab ?: 'Bab 1' }} · {{ $currentQuest->sub_bab ?: 'Sub 1' }}
                        </p>

                        <div class="mt-4">
                            <div class="flex items-center justify-between text-xs font-medium text-slate-400 mb-1.5">
                                <span>Progres Sub-Bab</span>
                                <span class="text-slate-800 dark:text-white font-semibold">{{ $qPct }}%</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-teal-600 transition-all duration-500"
                                    style="width: {{ $qPct }}%"></div>
                            </div>
                        </div>

                        <div class="mt-5 flex flex-col gap-2">
                            <a href="{{ route('siswa.materi.show', $currentQuest->id) }}"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition">
                                <span>{{ $qPct > 0 && $qPct < 100 ? 'Lanjutkan Studi Kasus' : 'Baca Sub-Bab Ini' }}</span>
                            </a>
                            @if($currentModul)
                                <a href="{{ route('siswa.materi', ['modul_id' => $currentModul->id]) }}"
                                    class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-800 px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-300 hover:border-teal-500/40 transition">
                                    Lihat Urutan Materi Modul
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Pilih salah satu modul di samping untuk mulai membaca sub-bab materi.
                        </p>
                    @endif
                </div>

                {{-- Pintasan Cepat ke Quiz & Statistik --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm space-y-3">
                    <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                        Evaluasi & Statistik
                    </h3>

                    <a href="{{ route('siswa.quiz') }}"
                        class="group flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/50 p-4 hover:border-teal-500/40 transition">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Evaluasi Quiz</p>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">{{ $stats['quiz_selesai'] }}/{{ $stats['total_quiz'] }} Paket Selesai</p>
                        </div>
                        <span class="text-slate-400 group-hover:text-teal-500 transition">→</span>
                    </a>

                    <a href="{{ route('siswa.statistik') }}"
                        class="group flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/50 p-4 hover:border-teal-500/40 transition">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Statistik Belajar</p>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">Grafik & Riwayat Progres</p>
                        </div>
                        <span class="text-slate-400 group-hover:text-teal-500 transition">→</span>
                    </a>
                </div>

            </div>

        </div>

    </div>

</x-layouts.siswa>
