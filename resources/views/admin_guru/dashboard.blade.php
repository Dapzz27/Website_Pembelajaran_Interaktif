<x-layouts.guru title="Dashboard Guru">

    @php
        $user = auth()->user();
        $modulList = \App\Models\Modul::with(['materi.studiKasus.pengumpulan'])->orderBy('urutan_modul', 'asc')->get();
        $totalModul = $modulList->count();
        $materiList = \App\Models\Materi::with(['modul', 'bagianMateri', 'studiKasus.pengumpulan'])->orderBy('modul_id', 'asc')->orderBy('urutan', 'asc')->get();
        $totalMateri = $materiList->count();
        $totalStudiKasus = $materiList->filter(fn ($m) => $m->studiKasus !== null || !empty(trim((string) $m->tipe_studi_kasus)))->count();
        $totalQuiz = \App\Models\Quiz::count();
        $siswaList = \App\Models\User::where('role', 0)->get();
        $totalSiswa = $siswaList->count();

        $rataProgresKelas = 0;
        if ($totalSiswa > 0) {
            $sumPct = 0;
            foreach ($siswaList as $s) {
                $sumPct += $s->getLearningStats()['progress_pct'];
            }
            $rataProgresKelas = (int) round($sumPct / $totalSiswa);
        }

        $pengumpulanTerbaru = \App\Models\PengumpulanStudiKasus::with(['siswa', 'studiKasus.materi'])
            ->latest('tanggal_kumpul')
            ->take(5)
            ->get();

        $aktivitasTerbaru = \App\Models\RiwayatBelajar::with('user')
            ->latest()
            ->take(6)
            ->get();
    @endphp

    <div class="max-w-6xl mx-auto space-y-6">

        {{-- HEADER SAPAAN GURU --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 lg:p-7 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">Workspace Pengajar</span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                        Selamat Datang, {{ $user->name }}
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                        Kelola materi pembelajaran, evaluasi quiz, dan pantau progres belajar siswa secara langsung.
                    </p>
                </div>
                <div class="flex-shrink-0 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 px-4 py-2.5 text-xs font-medium text-slate-400">
                    {{ now()->translatedFormat('l, d F Y') }}
                </div>
            </div>
        </div>

        {{-- ASYMMETRICAL 2-COLUMN WORKSPACE LAYOUT (8/12 KIRI + 4/12 KANAN) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- KOLOM UTAMA (KIRI - 8/12): UNIFIED OVERVIEW PANEL & AKTIVITAS / PENGUMPULAN SISWA --}}
            <div class="lg:col-span-8 space-y-6">

                {{-- Unified Overview Panel (Inline Metrics dengan Garis Vertikal Tipis divide-x) --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Ringkasan Kurikulum & Kelas
                        </h2>
                        <span class="text-xs font-medium text-slate-400">Data Real-Time</span>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 sm:gap-0 sm:divide-x sm:divide-slate-200 dark:sm:divide-slate-800 pt-2">
                        <div class="sm:pr-4">
                            <p class="text-xs font-medium text-slate-400">Total Modul</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                                {{ $totalModul }} <span class="text-sm font-normal text-slate-400">modul</span>
                            </p>
                        </div>
                        <div class="sm:px-4">
                            <p class="text-xs font-medium text-slate-400">Sub-Bab Materi</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                                {{ $totalMateri }} <span class="text-sm font-normal text-slate-400">materi</span>
                            </p>
                        </div>
                        <div class="sm:px-4">
                            <p class="text-xs font-medium text-slate-400">Studi Kasus</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                                {{ $totalStudiKasus }} <span class="text-sm font-normal text-slate-400">aktif</span>
                            </p>
                        </div>
                        <div class="sm:px-4">
                            <p class="text-xs font-medium text-slate-400">Paket Quiz</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                                {{ $totalQuiz }} <span class="text-sm font-normal text-slate-400">paket</span>
                            </p>
                        </div>
                        <div class="sm:pl-4">
                            <p class="text-xs font-medium text-slate-400">Total Siswa</p>
                            <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                                {{ $totalSiswa }} <span class="text-sm font-normal text-slate-400">siswa</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Daftar Pengumpulan Studi Kasus Terbaru yang Perlu Direview --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                Pengumpulan Studi Kasus Terbaru
                            </h2>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                                Daftar jawaban dan file lampiran studi kasus yang baru dikumpulkan siswa.
                            </p>
                        </div>
                        <a href="{{ route('guru.materi') }}"
                            class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline flex-shrink-0">
                            Lihat di Kelola Materi →
                        </a>
                    </div>

                    @if($pengumpulanTerbaru->isEmpty())
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 p-6 text-center">
                            <p class="text-sm text-slate-600 dark:text-slate-300">Belum ada pengumpulan studi kasus dari siswa.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-800 text-xs font-medium text-slate-400">
                                        <th class="pb-3 pr-4">Siswa</th>
                                        <th class="pb-3 px-4">Materi & Studi Kasus</th>
                                        <th class="pb-3 px-4">Waktu Kumpul</th>
                                        <th class="pb-3 pl-4 text-right">Tindakan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80 text-sm">
                                    @foreach($pengumpulanTerbaru as $sub)
                                        @php
                                            $mObj = $sub->studiKasus?->materi;
                                            $mJudul = $mObj ? ($mObj->judul_materi ?: $mObj->judul) : ($sub->studiKasus?->judul_studi_kasus ?? 'Studi Kasus');
                                        @endphp
                                        <tr>
                                            <td class="py-3.5 pr-4">
                                                <p class="font-semibold text-slate-800 dark:text-white">{{ $sub->siswa->name ?? 'Siswa' }}</p>
                                                <p class="text-xs font-medium text-slate-400">NIS: {{ $sub->siswa->nomor_induk ?? '-' }}</p>
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <p class="font-medium text-slate-800 dark:text-slate-200">{{ $mJudul }}</p>
                                                <p class="text-xs font-medium text-slate-400 truncate max-w-xs">
                                                    {{ \Illuminate\Support\Str::limit($sub->jawaban_teks, 60) }}
                                                </p>
                                            </td>
                                            <td class="py-3.5 px-4 text-xs font-medium text-slate-400 whitespace-nowrap">
                                                {{ $sub->tanggal_kumpul?->translatedFormat('d M Y, H:i') ?? '-' }}
                                            </td>
                                            <td class="py-3.5 pl-4 text-right whitespace-nowrap">
                                                @if($mObj)
                                                    <a href="{{ route('guru.materi.show', $mObj->id) }}"
                                                        class="inline-flex items-center rounded-lg bg-teal-500/10 hover:bg-teal-500/20 text-teal-600 dark:text-teal-400 border border-teal-500/20 px-3 py-1.5 text-xs font-semibold transition">
                                                        Review
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Aktivitas Belajar Siswa Terbaru --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4 mb-4">
                        <div>
                            <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                Aktivitas Belajar Siswa Terbaru
                            </h2>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                                Log penyelesaian bacaan materi, studi kasus, dan pengerjaan quiz oleh siswa.
                            </p>
                        </div>
                        <a href="{{ route('guru.data_siswa') }}"
                            class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline flex-shrink-0">
                            Data Siswa →
                        </a>
                    </div>

                    @if($aktivitasTerbaru->isEmpty())
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 p-6 text-center">
                            <p class="text-sm text-slate-600 dark:text-slate-300">Belum ada aktivitas belajar siswa yang tercatat.</p>
                        </div>
                    @else
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($aktivitasTerbaru as $akt)
                                <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-slate-800 dark:text-white truncate">
                                            <span class="font-semibold text-teal-600 dark:text-teal-400">{{ $akt->user->name ?? 'Siswa' }}</span>
                                            — {{ $akt->judul_aktivitas }}
                                        </p>
                                        <p class="text-xs font-medium text-slate-400 mt-0.5">
                                            {{ $akt->keterangan }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-medium text-slate-400 whitespace-nowrap flex-shrink-0">
                                        {{ $akt->created_at?->diffForHumans() }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>

            {{-- KOLOM SAMPING (KANAN - 4/12): AKSI CEPAT GURU & RINGKASAN PROGRES KELAS --}}
            <div class="lg:col-span-4 space-y-6">

                {{-- Panel Aksi Cepat Guru --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm space-y-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Aksi Cepat Guru
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Pintasan pengelolaan materi, evaluasi, dan data kelas.
                        </p>
                    </div>

                    <div class="space-y-2.5">
                        <a href="{{ route('guru.materi') }}"
                            class="group flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 p-3.5 hover:border-teal-500/40 transition">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Kelola Materi</p>
                                    <p class="text-xs font-medium text-slate-400">Susun modul & studi kasus</p>
                                </div>
                            </div>
                            <span class="text-slate-400 group-hover:text-teal-500 transition">→</span>
                        </a>

                        <a href="{{ route('guru.quiz') }}"
                            class="group flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 p-3.5 hover:border-teal-500/40 transition">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Kelola Quiz</p>
                                    <p class="text-xs font-medium text-slate-400">Atur paket soal & nilai KKM</p>
                                </div>
                            </div>
                            <span class="text-slate-400 group-hover:text-teal-500 transition">→</span>
                        </a>

                        <a href="{{ route('guru.data_siswa') }}"
                            class="group flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 p-3.5 hover:border-teal-500/40 transition">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white group-hover:text-teal-600 dark:group-hover:text-teal-400 transition">Data Siswa</p>
                                    <p class="text-xs font-medium text-slate-400">Pantau level & progres siswa</p>
                                </div>
                            </div>
                            <span class="text-slate-400 group-hover:text-teal-500 transition">→</span>
                        </a>
                    </div>
                </div>

                {{-- Ringkasan Distribusi Materi & Progres Kelas --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm space-y-4">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Distribusi Materi & Progres Kelas
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Rata-rata penyelesaian kurikulum seluruh siswa saat ini.
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-slate-400">Rata-rata Progres Kelas</span>
                            <span class="text-sm font-semibold text-teal-600 dark:text-teal-400">{{ $rataProgresKelas }}%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-teal-600 transition-all duration-500"
                                style="width: {{ $rataProgresKelas }}%"></div>
                        </div>
                    </div>

                    <div class="space-y-2.5 pt-1">
                        <div class="flex items-center justify-between text-xs font-medium text-slate-400">
                            <span>Daftar Modul Pembelajaran</span>
                            <span>Sub-Bab</span>
                        </div>
                        @forelse($modulList->take(6) as $modItem)
                            <div class="flex items-center justify-between gap-3 py-1.5 border-b border-slate-100 dark:border-slate-800/80 last:border-0">
                                <a href="{{ route('guru.materi', ['modul_id' => $modItem->id]) }}"
                                    class="text-sm font-medium text-slate-700 dark:text-slate-200 hover:text-teal-600 dark:hover:text-teal-400 truncate transition">
                                    {{ $modItem->urutan_modul }}. {{ $modItem->judul_modul }}
                                </a>
                                <span class="text-xs font-medium text-teal-600 dark:text-teal-400 flex-shrink-0">
                                    {{ $modItem->materi->count() }} materi
                                </span>
                            </div>
                        @empty
                            <p class="text-xs font-medium text-slate-400">Belum ada modul yang ditambahkan.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

    </div>

</x-layouts.guru>