<x-layouts.siswa title="Statistik Belajar">

    @once
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    @endonce

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- 1. PANEL UTAMA RINGKASAN PROGRES & STRIP METRIK INLINE --}}
        <section class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] shadow-sm overflow-hidden">

            <div class="p-6 lg:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

                    {{-- Kiri: Judul & Status Level --}}
                    <div class="max-w-2xl space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
                                Level {{ $stats['level'] }} · {{ $stats['rank_title'] }}
                            </span>
                            <span class="text-xs font-medium text-slate-400">
                                {{ $stats['completed_units'] }} / {{ $stats['total_units'] }} Unit Selesai
                            </span>
                        </div>

                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white pt-1">
                            Statistik & Progres Belajar
                        </h1>

                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Ringkasan capaian materi, penyelesaian studi kasus, dan nilai evaluasi quiz Anda.
                        </p>
                    </div>

                    {{-- Kanan: Progress Bar Solid Biru Tosca --}}
                    <div class="w-full lg:w-80 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-4 space-y-3 flex-shrink-0">
                        <div>
                            <div class="flex items-center justify-between text-xs font-medium text-slate-400 mb-1.5">
                                <span>Ketuntasan Kurikulum</span>
                                <span class="text-sm font-semibold text-teal-600 dark:text-teal-400">{{ $stats['progress_pct'] }}%</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-teal-600 transition-all duration-700 ease-out"
                                    style="width: {{ $stats['progress_pct'] }}%"></div>
                            </div>
                        </div>

                        <div class="pt-2.5 border-t border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between text-xs font-medium text-slate-400 mb-1.5">
                                <span>Progres ke Level {{ $stats['level'] + 1 }}</span>
                                <span class="font-semibold text-slate-800 dark:text-white">{{ $stats['next_level_pct'] }}%</span>
                            </div>
                            <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                <div class="h-full rounded-full bg-teal-500 transition-all duration-700 ease-out"
                                    style="width: {{ $stats['next_level_pct'] }}%"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Strip Metrik Inline Ringkas (Dipisahkan Garis Vertikal Tipis divide-x) --}}
            <div class="border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-[#061024]/70 px-6 py-4 lg:px-8">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-0 lg:divide-x lg:divide-slate-200 dark:lg:divide-slate-800">

                    <div class="lg:pr-6">
                        <p class="text-xs font-medium text-slate-400">Materi Selesai</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $stats['materi_selesai'] }} <span class="text-sm font-normal text-slate-400">/ {{ $stats['total_materi'] }}</span>
                        </p>
                    </div>

                    <div class="lg:px-6">
                        <p class="text-xs font-medium text-slate-400">Studi Kasus Tuntas</p>
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
                            {{ $stats['rata_skor_quiz'] }} <span class="text-sm font-normal text-slate-400">/ 100</span>
                        </p>
                    </div>

                </div>
            </div>
        </section>

        {{-- 2. GRAFIK PERKEMBANGAN BELAJAR (CHART.JS) --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Grafik 1: Ketuntasan Materi & Studi Kasus --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Progres Penyelesaian Materi & Studi Kasus
                        </h2>
                        <p class="text-xs font-medium text-slate-400 mt-0.5">
                            0% = Belum dibaca · 50% = Sudah baca materi · 100% = Studi kasus selesai
                        </p>
                    </div>
                </div>

                @if(count($materiChartLabels) > 0)
                    <div class="relative w-full h-64">
                        <canvas id="materiProgressChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-center p-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                        <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum Ada Data Materi</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Grafik akan tampil setelah guru menambahkan materi pembelajaran.</p>
                    </div>
                @endif
            </div>

            {{-- Grafik 2: Perolehan Skor Quiz --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Grafik Skor Tertinggi Quiz
                        </h2>
                        <p class="text-xs font-medium text-slate-400 mt-0.5">
                            Perbandingan nilai tertinggi Anda pada setiap paket quiz (Skala 0 - 100)
                        </p>
                    </div>
                    <a href="{{ route('siswa.quiz') }}"
                        class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                        Buka Quiz →
                    </a>
                </div>

                @if(count($quizChartLabels) > 0)
                    <div class="relative w-full h-64">
                        <canvas id="quizScoreChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-center p-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl">
                        <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum Ada Paket Quiz</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Grafik skor akan muncul saat paket quiz tersedia.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- 3. DAFTAR STUDI KASUS BELUM SELESAI & RIWAYAT PEMBELAJARAN --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            {{-- Kolom Kiri (5/12): Studi Kasus Belum Selesai --}}
            <div class="xl:col-span-5 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                Studi Kasus Belum Selesai
                            </h2>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">
                                Daftar studi kasus yang menunggu untuk diselesaikan.
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                            {{ $studiKasusBelum->count() }} Tersisa
                        </span>
                    </div>

                    @if($studiKasusBelum->isEmpty())
                        <div class="rounded-xl border border-teal-500/25 bg-teal-500/5 p-6 text-center my-2">
                            <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Semua Studi Kasus Selesai</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                Anda telah menyelesaikan seluruh studi kasus yang tersedia saat ini.
                            </p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($studiKasusBelum as $sk)
                                <div class="rounded-xl border {{ $sk->lock_state === 'terbuka' ? 'border-teal-500/30 bg-teal-500/5' : 'border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/50' }} p-4 transition-colors">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                                @if($sk->modul)
                                                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                                                        {{ $sk->modul->judul_modul }}
                                                    </span>
                                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                                @endif
                                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                                                    Sub-Bab #{{ $sk->urutan }}
                                                </span>
                                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                                <span class="text-xs font-medium text-slate-400">
                                                    {{ $sk->tipe_studi_kasus }}
                                                </span>
                                                <span class="text-slate-300 dark:text-slate-700">•</span>
                                                @if($sk->lock_state === 'terbuka')
                                                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">Terbuka</span>
                                                @else
                                                    <span class="text-xs font-medium text-slate-400">Terkunci</span>
                                                @endif
                                            </div>

                                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white truncate">
                                                {{ $sk->judul_materi ?: $sk->judul }}
                                            </h3>

                                            @if($sk->lock_state === 'terkunci' && $sk->prev_judul)
                                                <p class="text-xs font-medium text-slate-400 mt-1">
                                                    Selesaikan "{{ $sk->prev_judul }}" terlebih dahulu.
                                                </p>
                                            @else
                                                <p class="text-xs font-medium text-slate-400 mt-1 line-clamp-1">
                                                    {{ $sk->soal_studi_kasus }}
                                                </p>
                                            @endif
                                        </div>

                                        <div class="flex-shrink-0">
                                            @if($sk->lock_state === 'terbuka')
                                                <a href="{{ route('siswa.materi.show', $sk->id) }}"
                                                    class="inline-flex items-center rounded-xl bg-teal-600 hover:bg-teal-700 px-3.5 py-2 text-xs font-semibold text-white transition">
                                                    Kerjakan
                                                </a>
                                            @else
                                                <a href="{{ route('siswa.materi', ['modul_id' => $sk->modul_id]) }}"
                                                    class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-medium text-slate-500 dark:text-slate-400 hover:border-teal-500/40 transition">
                                                    Lihat Alur
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs font-medium text-slate-400">
                    <span>Lihat seluruh daftar modul</span>
                    <a href="{{ route('siswa.materi') }}" class="font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                        Buka Daftar Materi →
                    </a>
                </div>
            </div>

            {{-- Kolom Kanan (7/12): Riwayat Aktivitas Belajar --}}
            <div class="xl:col-span-7 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Riwayat Aktivitas Belajar
                        </h2>
                        <p class="text-xs font-medium text-slate-400 mt-0.5">
                            Catatan kronologis setiap materi, studi kasus, dan quiz yang telah Anda kerjakan.
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                        {{ $riwayat->count() }} Aktivitas
                    </span>
                </div>

                @if($riwayat->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center">
                        <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum Ada Riwayat Aktivitas</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 max-w-md mx-auto mt-1">
                            Mulai pelajari materi pertama atau kerjakan quiz untuk melihat catatan aktivitas belajar Anda.
                        </p>
                        <div class="mt-4 flex justify-center">
                            <a href="{{ route('siswa.materi') }}"
                                class="rounded-xl bg-teal-600 hover:bg-teal-500 px-4 py-2.5 text-xs font-semibold text-white transition">
                                Mulai Materi Pertama
                            </a>
                        </div>
                    </div>
                @else
                    <div class="relative pl-6 border-l border-slate-200 dark:border-slate-800 space-y-3.5 max-h-[430px] overflow-y-auto pr-2">
                        @foreach($riwayat as $log)
                            @php
                                $badgeLabel = match($log->tipe_aktivitas) {
                                    'studi_kasus' => 'Studi Kasus',
                                    'quiz' => 'Evaluasi Quiz',
                                    default => 'Baca Materi',
                                };
                            @endphp
                            <div class="relative rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-4">
                                <span class="absolute -left-[29px] top-5 w-2.5 h-2.5 rounded-full bg-teal-500 ring-4 ring-white dark:ring-[#08142c]"></span>

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                                            {{ $badgeLabel }}
                                        </span>
                                        @if(!is_null($log->skor))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                                                Skor: {{ $log->skor }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium text-slate-400">
                                        {{ $log->created_at ? $log->created_at->diffForHumans() : 'Baru saja' }}
                                        ({{ $log->created_at ? $log->created_at->format('d M Y, H:i') : '' }})
                                    </span>
                                </div>

                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white mt-2">
                                    {{ $log->judul_aktivitas }}
                                </h4>
                                @if($log->keterangan)
                                    <p class="text-xs font-medium text-slate-400 mt-0.5">
                                        {{ $log->keterangan }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const materiLabels = @json($materiChartLabels);
            const materiData = @json($materiChartProgress);

            const ctxMateri = document.getElementById('materiProgressChart');
            if (ctxMateri && window.Chart && materiLabels.length > 0) {
                new Chart(ctxMateri, {
                    type: 'bar',
                    data: {
                        labels: materiLabels,
                        datasets: [{
                            label: 'Ketuntasan Materi (%)',
                            data: materiData,
                            backgroundColor: materiData.map(v => v >= 100 ? 'rgba(13, 148, 136, 0.85)' : (v >= 50 ? 'rgba(20, 184, 166, 0.55)' : 'rgba(148, 163, 184, 0.3)')),
                            borderColor: materiData.map(v => v >= 100 ? '#0d9488' : (v >= 50 ? '#14b8a6' : '#64748b')),
                            borderWidth: 1.5,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Progres: ' + ctx.parsed.y + '%'
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { size: 11 } }
                            },
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 25,
                                    color: '#94a3b8',
                                    font: { size: 11 },
                                    callback: v => v + '%'
                                },
                                grid: { color: 'rgba(148, 163, 184, 0.12)' }
                            }
                        }
                    }
                });
            }

            const quizLabels = @json($quizChartLabels);
            const quizScores = @json($quizChartScores);

            const ctxQuiz = document.getElementById('quizScoreChart');
            if (ctxQuiz && window.Chart && quizLabels.length > 0) {
                new Chart(ctxQuiz, {
                    type: 'bar',
                    data: {
                        labels: quizLabels,
                        datasets: [{
                            label: 'Skor Tertinggi',
                            data: quizScores,
                            backgroundColor: 'rgba(13, 148, 136, 0.8)',
                            borderColor: '#0d9488',
                            borderWidth: 1.5,
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => 'Nilai Tertinggi: ' + ctx.parsed.y + ' / 100'
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: '#94a3b8', font: { size: 11 } }
                            },
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    stepSize: 20,
                                    color: '#94a3b8',
                                    font: { size: 11 }
                                },
                                grid: { color: 'rgba(148, 163, 184, 0.12)' }
                            }
                        }
                    }
                });
            }
        });
    </script>

</x-layouts.siswa>