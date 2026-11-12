<x-layouts.guru title="Data Siswa">

    @once
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
    @endonce

    @php
        $siswaUsers = \App\Models\User::where('role', 0)->orderBy('name', 'asc')->get();

        $siswa = $siswaUsers->map(function ($u) {
            $st = $u->getLearningStats();
            $hasActivity = $st['completed_units'] > 0
                || \App\Models\SiswaMateriProgress::where('siswa_id', $u->id)->exists()
                || \App\Models\QuizAttempt::where('user_id', $u->id)->exists();

            $status = $st['progress_pct'] >= 50
                ? 'aktif'
                : ($hasActivity ? 'aktif' : 'nonaktif');

            $words = preg_split('/\s+/', trim($u->name));
            $initials = strtoupper(substr($words[0] ?? 'S', 0, 1) . substr($words[1] ?? ($words[0] ?? 'W'), 0, 1));

            return [
                'id' => $u->id,
                'nama' => $u->name,
                'nis' => $u->nomor_induk,
                'kelas' => $st['rank_title'],
                'status' => $status,
                'level' => $st['level'],
                'progress' => $st['progress_pct'],
                'materi_selesai' => $st['materi_selesai'],
                'quiz_selesai' => $st['quiz_selesai'],
                'rata_skor_quiz' => $st['rata_skor_quiz'],
                'avatar' => $initials,
            ];
        });

        $totalSiswa = $siswa->count();
        $aktif = $siswa->where('status', 'aktif')->count();
        $avgProgress = $totalSiswa > 0 ? (int) round($siswa->avg('progress')) : 0;
        $avgLevel = $totalSiswa > 0 ? round($siswa->avg('level'), 1) : 0;

        $semuaMateri = \App\Models\Materi::orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();
        $materiProgress = $semuaMateri->map(function ($m) use ($totalSiswa) {
            $selesaiCount = \App\Models\SiswaMateriProgress::where('materi_id', $m->id)
                ->where('status', 'selesai')
                ->count();
            $pct = $totalSiswa > 0 ? (int) round(($selesaiCount / $totalSiswa) * 100) : 0;
            return [
                'nama' => $m->judul_materi ?: $m->judul,
                'pct' => min(100, $pct),
                'selesai' => $selesaiCount,
            ];
        });

        $riwayatTerkini = \App\Models\RiwayatBelajar::with('user')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        $aktivitas = $riwayatTerkini->map(function ($r) {
            $namaUser = e($r->user->name ?? 'Siswa');
            $judul = e($r->judul_aktivitas);
            return [
                'waktu' => $r->created_at ? $r->created_at->diffForHumans() : 'Baru saja',
                'tipe' => $r->tipe_aktivitas,
                'teks' => "<span class='font-semibold text-slate-900 dark:text-white'>{$namaUser}</span> — {$judul}" . (!is_null($r->skor) ? " <span class='text-teal-600 dark:text-teal-400 font-semibold'>(Skor: {$r->skor})</span>" : ''),
            ];
        });

        $chartLabels = $siswa->pluck('nama')->values();
        $chartData = $siswa->pluck('progress')->values();
    @endphp

    {{-- HEADER & UNIFIED INLINE METRIC STRIP --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 pb-6 border-b border-slate-100 dark:border-slate-800/80">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    Data Siswa
                </h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                    Pantau akun, progres belajar, level, dan aktivitas siswa.
                </p>
            </div>
            <span class="text-xs font-medium text-slate-400">
                {{ now()->translatedFormat('d F Y') }}
            </span>
        </div>

        {{-- Compact Inline Metric Strip (divide-x) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x divide-slate-200 dark:divide-slate-800 pt-5">
            <div class="py-2 sm:py-0 sm:px-5 first:pl-0">
                <p class="text-xs font-medium text-slate-400">Total Siswa</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $totalSiswa }} <span class="text-sm font-normal text-slate-400">akun</span>
                </p>
            </div>
            <div class="py-2 sm:py-0 sm:px-5">
                <p class="text-xs font-medium text-slate-400">Aktif Belajar</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-teal-600 dark:text-teal-400">
                    {{ $aktif }} <span class="text-sm font-normal text-slate-400">siswa</span>
                </p>
            </div>
            <div class="py-2 sm:py-0 sm:px-5">
                <p class="text-xs font-medium text-slate-400">Rata-rata Progres</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $avgProgress }}<span class="text-sm font-normal text-slate-400">%</span>
                </p>
            </div>
            <div class="py-2 sm:py-0 sm:px-5 last:pr-0">
                <p class="text-xs font-medium text-slate-400">Rata-rata Level</p>
                <p class="mt-1 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $avgLevel }} <span class="text-sm font-normal text-slate-400">level</span>
                </p>
            </div>
        </div>
    </div>

    {{-- TABEL SISWA + CHART --}}
    <div class="grid xl:grid-cols-12 gap-5 mt-5">

        {{-- Daftar Akun Siswa (7/12) --}}
        <div class="xl:col-span-7 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                    Daftar Akun Siswa
                </h2>
                <span class="text-xs font-medium text-slate-400">{{ $totalSiswa }} siswa terdaftar</span>
            </div>

            <div class="relative mb-4">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path stroke-linecap="round" d="m21 21-4.3-4.3"></path>
                </svg>
                <input type="text" id="searchSiswa" placeholder="Cari nama atau nomor induk siswa..."
                    class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 pl-10 pr-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500 transition" />
            </div>

            <div id="siswaList" class="divide-y divide-slate-100 dark:divide-slate-800/70">
                @forelse($siswa as $s)
                    @php
                        $statusBadge = $s['status'] === 'aktif'
                            ? 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/20'
                            : 'bg-slate-500/10 text-slate-500 dark:text-slate-400 border-slate-500/20';
                        $statusLabel = $s['status'] === 'aktif' ? 'Aktif' : 'Belum Mulai';
                    @endphp
                    <div class="siswa-row flex items-center gap-3.5 py-3 px-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 transition"
                        data-nama="{{ strtolower($s['nama'] . ' ' . $s['nis']) }}">
                        <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            {{ $s['avatar'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $s['nama'] }}</p>
                            <p class="text-xs font-medium text-slate-400">NIS: {{ $s['nis'] }} · Level {{ $s['level'] }} ({{ $s['kelas'] }})</p>
                        </div>
                        <div class="hidden sm:flex items-center gap-2.5 flex-shrink-0">
                            <div class="w-24 h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                <div class="h-1.5 rounded-full bg-teal-600" style="width:{{ $s['progress'] }}%;"></div>
                            </div>
                            <span class="text-xs font-medium text-slate-500 dark:text-slate-400 w-9 text-right">{{ $s['progress'] }}%</span>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg border text-xs font-medium flex-shrink-0 {{ $statusBadge }}">
                            {{ $statusLabel }}
                        </span>
                    </div>
                @empty
                    <p class="text-center text-sm text-slate-400 py-8">Belum ada akun siswa yang terdaftar.</p>
                @endforelse
            </div>
            <p id="emptySearch" class="hidden text-center text-sm text-slate-400 py-6">Siswa tidak ditemukan.</p>
        </div>

        {{-- Chart + Progres Materi (5/12) --}}
        <div class="xl:col-span-5 flex flex-col gap-5">

            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mb-4">
                    Progres Belajar per Siswa
                </h2>
                <div style="position:relative; width:100%; height:200px;">
                    <canvas id="progressChart" role="img" aria-label="Grafik progres belajar siswa"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
                <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mb-4">
                    Penyelesaian per Materi
                </h2>
                <div class="space-y-3.5">
                    @forelse($materiProgress as $m)
                        <div>
                            <div class="flex justify-between text-xs mb-1.5">
                                <span class="text-sm text-slate-600 dark:text-slate-300 truncate pr-2">{{ $m['nama'] }}</span>
                                <span class="text-xs font-medium text-slate-400 flex-shrink-0">{{ $m['selesai'] }}/{{ $totalSiswa }} ({{ $m['pct'] }}%)</span>
                            </div>
                            <div class="h-2 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                <div class="h-2 rounded-full bg-teal-600 transition-all duration-500" style="width:{{ $m['pct'] }}%;"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 text-center py-4">Belum ada materi yang tersedia.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- PERINGKAT + AKTIVITAS --}}
    <div class="grid xl:grid-cols-2 gap-5 mt-5">

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
            <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mb-4">
                Peringkat Progres Siswa
            </h2>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/70">
                @forelse($siswa->sortByDesc('progress')->values()->take(5) as $idx => $s)
                    <div class="flex items-center gap-3.5 py-3 px-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <span class="text-xs font-semibold w-6 h-6 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center flex-shrink-0">
                            {{ $idx + 1 }}
                        </span>
                        <div class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center text-xs font-semibold flex-shrink-0">
                            {{ $s['avatar'] }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $s['nama'] }}</p>
                            <p class="text-xs font-medium text-slate-400">{{ $s['materi_selesai'] }} Materi · {{ $s['quiz_selesai'] }} Quiz</p>
                        </div>
                        <span class="text-sm font-semibold text-teal-600 dark:text-teal-400">
                            Lv. {{ $s['level'] }} ({{ $s['progress'] }}%)
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">Belum ada data peringkat siswa.</p>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">
            <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mb-4">
                Aktivitas Belajar Terkini
            </h2>
            <div class="divide-y divide-slate-100 dark:divide-slate-800/70">
                @forelse($aktivitas as $a)
                    <div class="flex items-center gap-3.5 py-3 px-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-600 dark:text-slate-300 leading-snug">{!! $a['teks'] !!}</p>
                            <p class="text-xs font-medium text-slate-400 mt-0.5">{{ $a['waktu'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-400 text-center py-6">Belum ada aktivitas belajar siswa yang tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- CHART SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('progressChart');
            const labels = @json($chartLabels);
            const dataVals = @json($chartData);

            if (ctx && window.Chart) {
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels.length ? labels : ['Belum ada siswa'],
                        datasets: [{
                            label: 'Progres (%)',
                            data: dataVals.length ? dataVals : [0],
                            backgroundColor: '#0d9488',
                            borderRadius: 6,
                            borderSkipped: false,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ctx.parsed.y + '%' } }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 }, color: '#94a3b8' }
                            },
                            y: {
                                min: 0, max: 100,
                                ticks: { callback: v => v + '%', font: { size: 10 }, color: '#94a3b8', stepSize: 25 },
                                grid: { color: 'rgba(148,163,184,0.1)' }
                            }
                        }
                    }
                });
            }

            const input = document.getElementById('searchSiswa');
            const empty = document.getElementById('emptySearch');
            if (input) {
                input.addEventListener('input', function () {
                    const q = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.siswa-row');
                    let found = 0;
                    rows.forEach(row => {
                        const match = row.dataset.nama.includes(q);
                        row.style.display = match ? '' : 'none';
                        if (match) found++;
                    });
                    if (empty) empty.classList.toggle('hidden', found > 0);
                });
            }
        });
    </script>

</x-layouts.guru>