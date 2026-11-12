<x-layouts.guru title="Kelola Materi">

    @php
        $allModulJson = $modulList->map(function ($mod) {
            return [
                'id' => (int) $mod->id,
                'judul_modul' => $mod->judul_modul,
                'kategori_atau_kelas' => $mod->kategori_atau_kelas ?: '',
                'deskripsi_singkat' => $mod->deskripsi_singkat ?: '',
                'urutan_modul' => (int) $mod->urutan_modul,
            ];
        })->keyBy('id');

        $allMateriJson = $materi->map(function ($item) {
            $judul = $item->judul_materi ?: $item->judul;
            $hasVideo = (bool) $item->has_video
                || !empty($item->video_url)
                || $item->bagianMateri->contains(fn ($b) => $b->tipe_media === 'video' && !empty($b->url_video));
            $kasusObj = $item->studiKasus;
            $tipeKasus = $kasusObj?->tipe_studi_kasus ?? ($item->tipe_studi_kasus ?: '');
            $judulKasus = $kasusObj?->judul_studi_kasus ?? ('Studi Kasus: ' . $judul);
            $soalKasus = $kasusObj?->instruksi_soal ?? ($item->soal_studi_kasus ?: '');
            $blocksData = $item->bagianMateri->map(fn ($b) => [
                'id' => $b->id,
                'tipe_layout' => $b->tipe_layout ?: 'teks_penuh',
                'sub_judul' => $b->sub_judul ?: '',
                'isi_teks' => $b->isi_teks ?: '',
                'tipe_media' => $b->tipe_media ?: 'none',
                'existing_gambar' => $b->file_gambar ?: '',
                'gambar_url' => $b->gambar_url ?: '',
                'url_video' => $b->url_video ?: '',
            ])->values();

            return [
                'id' => (int) $item->id,
                'modul_id' => (int) $item->modul_id,
                'judul_materi' => $judul,
                'bab' => $item->bab ?: 'Bab 1',
                'sub_bab' => $item->sub_bab ?: 'Sub 1',
                'urutan' => (int) $item->urutan,
                'konten_materi' => $item->konten_materi ?: $item->deskripsi ?: '',
                'has_video' => $hasVideo,
                'video_url' => $item->video_url ?: '',
                'judul_studi_kasus' => $judulKasus,
                'tipe_studi_kasus' => $tipeKasus ?: 'Esai singkat',
                'soal_studi_kasus' => $soalKasus,
                'opsi_a' => $kasusObj?->opsi_a ?? ($item->opsi_a ?: ''),
                'opsi_b' => $kasusObj?->opsi_b ?? ($item->opsi_b ?: ''),
                'opsi_c' => $kasusObj?->opsi_c ?? ($item->opsi_c ?: ''),
                'opsi_d' => $kasusObj?->opsi_d ?? ($item->opsi_d ?: ''),
                'blocks' => $blocksData,
            ];
        })->keyBy('id');
    @endphp

    <div x-data="kelolaMateriPage({
        activeModulId: {{ $activeModul ? (int) $activeModul->id : 'null' }},
        nextModulUrutan: {{ $totalModul + 1 }},
        nextUrutan: {{ $totalMateri + 1 }},
        modulStoreUrl: '{{ route('guru.modul.store') }}',
        modulUpdateBaseUrl: '{{ url('admin_guru/modul') }}',
        modulDeleteBaseUrl: '{{ url('admin_guru/modul') }}',
        storeUrl: '{{ route('guru.materi.store') }}',
        updateBaseUrl: '{{ url('admin_guru/materi') }}',
        deleteBaseUrl: '{{ url('admin_guru/materi') }}',
        modulMap: {{ Js::from($allModulJson) }},
        materiMap: {{ Js::from($allMateriJson) }},
        autoEditId: {{ request()->filled('edit') ? (int) request()->query('edit') : 'null' }}
    })" x-init="initPage()" class="max-w-6xl mx-auto space-y-6">

        {{-- NOTIFIKASI --}}
        @if(session('success'))
            <div x-data="{ showAlert: true }" x-show="showAlert" x-transition.duration.300ms
                class="flex items-center justify-between gap-3 rounded-xl border border-teal-500/25 bg-teal-500/10 px-5 py-3.5 text-sm font-medium text-teal-700 dark:text-teal-300">
                <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-teal-500/15 text-teal-600 dark:text-teal-400 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" @click="showAlert = false"
                    class="rounded-lg p-1.5 text-teal-600 dark:text-teal-300 hover:bg-teal-500/20 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ showAlert: true }" x-show="showAlert" x-transition.duration.300ms
                class="flex items-center justify-between gap-3 rounded-xl border border-red-500/25 bg-red-500/10 px-5 py-3.5 text-sm font-medium text-red-700 dark:text-red-300">
                <span>{{ session('error') }}</span>
                <button type="button" @click="showAlert = false"
                    class="rounded-lg p-1.5 text-red-600 dark:text-red-300 hover:bg-red-500/20 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div x-data="{ showAlert: true }" x-show="showAlert" x-transition.duration.300ms
                class="rounded-xl border border-red-500/25 bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
                <div class="flex items-start justify-between gap-3">
                    <ul class="space-y-1">
                        @foreach($errors->all() as $err)
                            <li>• {{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" @click="showAlert = false"
                        class="rounded-lg p-1 text-red-600 dark:text-red-300 hover:bg-red-500/20 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- =====================================================================
             TAHAP 1: DAFTAR MODUL PEMBELAJARAN (SAAT BELUM MEMILIH MODUL)
             ===================================================================== --}}
        @if(!$activeModul)

            {{-- HEADER DAFTAR MODUL --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 lg:p-7 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="max-w-xl">
                        <span class="text-xs font-medium text-teal-600 dark:text-teal-400">Manajemen Kurikulum</span>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            Modul Pembelajaran
                        </h1>
                        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                            Pilih modul untuk mengelola urutan sub-bab materi dan studi kasus di dalamnya, atau tambahkan modul baru.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-5">
                        <div class="flex items-center divide-x divide-slate-200 dark:divide-slate-800 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 px-5 py-3">
                            <div class="pr-4 sm:pr-5">
                                <p class="text-xs font-medium text-slate-400">Total Modul</p>
                                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                                    {{ $totalModul }} <span class="text-sm font-normal text-slate-400">modul</span>
                                </p>
                            </div>
                            <div class="px-4 sm:px-5">
                                <p class="text-xs font-medium text-slate-400">Sub-Bab Materi</p>
                                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                                    {{ $totalMateriAll }} <span class="text-sm font-normal text-slate-400">materi</span>
                                </p>
                            </div>
                            <div class="pl-4 sm:pl-5">
                                <p class="text-xs font-medium text-slate-400">Studi Kasus</p>
                                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                                    {{ $totalStudiKasusAll }} <span class="text-sm font-normal text-slate-400">aktif</span>
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="openCreateModulModal()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Tambah Modul</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- DAFTAR KARTU / BARIS MODUL --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Daftar Modul Tersedia
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Klik pada modul untuk mengatur urutan sub-bab materi dan studi kasus di dalamnya.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    @forelse($modulList as $mIdx => $mod)
                        @php
                            $isFirstMod = $mIdx === 0;
                            $isLastMod = $mIdx === ($modulList->count() - 1);
                        @endphp

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-5 sm:p-6 shadow-sm hover:border-teal-500/40 transition-colors">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

                                {{-- Info Modul Kiri --}}
                                <div class="flex items-start gap-4 min-w-0 flex-1">
                                    <div class="w-11 h-11 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 font-bold text-sm flex items-center justify-center flex-shrink-0 mt-0.5">
                                        {{ $mod->urutan_modul }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2.5">
                                            <a href="{{ route('guru.materi', ['modul_id' => $mod->id]) }}"
                                                class="group inline-block">
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
                                            {{ $mod->deskripsi_singkat ?: 'Modul pembelajaran terstruktur beserta latihan studi kasus.' }}
                                        </p>

                                        {{-- Ringkasan Sub-Bab & Studi Kasus di dalam Modul --}}
                                        <div class="flex flex-wrap items-center gap-2.5 mt-3.5">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                                <span>{{ $mod->jumlah_materi }} Sub-Bab Materi</span>
                                            </span>

                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-700 dark:text-teal-300 border border-teal-500/20">
                                                <span>{{ $mod->jumlah_studi_kasus }} Studi Kasus</span>
                                            </span>

                                            @if($mod->jumlah_video > 0)
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                    <span>{{ $mod->jumlah_video }} Materi Bervideo</span>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Tombol Aksi Modul Kanan --}}
                                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                    <a href="{{ route('guru.materi', ['modul_id' => $mod->id]) }}"
                                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition">
                                        <span>Kelola Materi ({{ $mod->jumlah_materi }})</span>
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </a>

                                    {{-- Urutan Modul Naik --}}
                                    <form action="{{ route('guru.modul.urutan', $mod->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" @if($isFirstMod) disabled @endif
                                            title="Pindahkan modul naik"
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 transition {{ $isFirstMod ? 'opacity-40 cursor-not-allowed' : 'hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400' }}">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Urutan Modul Turun --}}
                                    <form action="{{ route('guru.modul.urutan', $mod->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" @if($isLastMod) disabled @endif
                                            title="Pindahkan modul turun"
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 transition {{ $isLastMod ? 'opacity-40 cursor-not-allowed' : 'hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400' }}">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </form>

                                    {{-- Edit Modul --}}
                                    <button type="button"
                                        @click="openEditModulModalById({{ $mod->id }})"
                                        title="Edit modul"
                                        class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>

                                    {{-- Hapus Modul --}}
                                    <button type="button"
                                        @click="openDeleteModulModal({{ $mod->id }}, @js($mod->judul_modul))"
                                        title="Hapus modul"
                                        class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:border-red-400 hover:text-red-500 dark:hover:border-red-400/50 dark:hover:text-red-400 transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-12 text-center shadow-sm">
                            <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum ada modul pembelajaran</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Klik tombol + Tambah Modul di atas untuk membuat modul pembelajaran pertama.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        @else

        {{-- =====================================================================
             TAHAP 2: DETAIL ALUR MATERI DI DALAM MODUL YANG DIPILIH
             ===================================================================== --}}

            {{-- NAVIGASI KEMBALI KE DAFTAR MODUL --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('guru.materi') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] px-4 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400 shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>← Kembali ke Daftar Modul</span>
                </a>

                <div class="flex items-center gap-2">
                    <button type="button" @click="openEditModulModalById({{ $activeModul->id }})"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:border-teal-500/40 hover:text-teal-600 dark:hover:text-teal-400 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Info Modul</span>
                    </button>
                </div>
            </div>

            {{-- HEADER MODUL AKTIF & RINGKASAN TERPADU --}}
            <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 lg:p-7 shadow-sm">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="max-w-2xl">
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
                            {{ $activeModul->deskripsi_singkat ?: 'Kelola urutan sub-bab materi bacaan dan studi kasus di dalam modul ini.' }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-5">
                        <div class="flex items-center divide-x divide-slate-200 dark:divide-slate-800 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 px-5 py-3">
                            <div class="pr-5">
                                <p class="text-xs font-medium text-slate-400">Sub-Bab Materi</p>
                                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                                    {{ $totalMateri }} <span class="text-sm font-normal text-slate-400">materi</span>
                                </p>
                            </div>
                            <div class="pl-5">
                                <p class="text-xs font-medium text-slate-400">Studi Kasus</p>
                                <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                                    {{ $totalStudiKasus }} <span class="text-sm font-normal text-slate-400">aktif</span>
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="openCreateModal()"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>+ Tambah Materi</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- DAFTAR URUTAN SUB-BAB MATERI DI DALAM MODUL --}}
            <div class="space-y-4">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                        Alur Pembelajaran & Urutan Materi
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                        Sub-bab urutan pertama di dalam modul selalu terbuka. Sub-bab berikutnya terbuka otomatis setelah siswa menuntaskan materi sebelumnya.
                    </p>
                </div>

                <div class="space-y-3.5">
                    @forelse($materi as $index => $item)
                        @php
                            $judul = $item->judul_materi ?: $item->judul;
                            $prevJudul = $index > 0 ? ($materi[$index - 1]->judul_materi ?: $materi[$index - 1]->judul) : null;
                            $isFirst = $index === 0;
                            $isLast = $index === ($materi->count() - 1);

                            $hasVideo = (bool) $item->has_video
                                || !empty($item->video_url)
                                || $item->bagianMateri->contains(fn ($b) => $b->tipe_media === 'video' && !empty($b->url_video));
                            $hasGambar = $item->bagianMateri->contains(fn ($b) => $b->tipe_media === 'gambar' && !empty($b->file_gambar));

                            $kasusObj = $item->studiKasus;
                            $tipeKasus = $kasusObj?->tipe_studi_kasus ?? ($item->tipe_studi_kasus ?: '');
                            $submissions = $kasusObj ? $kasusObj->pengumpulan : collect();
                            $submissionsJson = $submissions->map(fn ($s) => [
                                'id' => $s->id,
                                'siswa_nama' => $s->siswa->name ?? 'Siswa',
                                'siswa_nis' => $s->siswa->nomor_induk ?? '-',
                                'jawaban_teks' => $s->jawaban_teks ?: '-',
                                'file_url' => $s->file_url,
                                'nama_file_asli' => $s->nama_file_asli ?: ($s->file_lampiran ? basename($s->file_lampiran) : null),
                                'tipe_file' => $s->tipe_file ?: '',
                                'tanggal' => $s->tanggal_kumpul ? $s->tanggal_kumpul->translatedFormat('d M Y, H:i') : '-',
                            ])->values();
                        @endphp

                        <div class="relative flex items-stretch gap-3 sm:gap-4">
                            {{-- Indikator Timeline Kiri --}}
                            <div class="flex flex-col items-center flex-shrink-0">
                                @if($isFirst)
                                    <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                        1
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center flex-shrink-0 text-xs font-semibold">
                                        {{ $index + 1 }}
                                    </div>
                                @endif

                                @if(!$isLast)
                                    <div class="w-px flex-1 bg-slate-200 dark:bg-slate-800 mt-2"></div>
                                @endif
                            </div>

                            {{-- Card Utama Materi --}}
                            <div class="flex-1 min-w-0 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-5 shadow-sm hover:border-teal-500/40 transition-colors">
                                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('guru.materi.show', $item->id) }}" class="hover:text-teal-600 dark:hover:text-teal-400 transition">
                                                <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                                    {{ $judul }}
                                                </h3>
                                            </a>
                                        </div>

                                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                                            {{ $item->bab ?: 'Bab 1' }} · {{ $item->sub_bab ?: 'Sub 1' }}
                                            <span class="mx-1.5 text-slate-300 dark:text-slate-600">•</span>
                                            <span class="text-xs font-medium text-teal-600 dark:text-teal-400">{{ max(1, $item->bagianMateri->count()) }} Bagian Konten</span>
                                        </p>

                                        {{-- Deretan Badge Informasi --}}
                                        <div class="flex flex-wrap items-center gap-2 mt-3">
                                            @if($isFirst)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-700 dark:text-teal-300 border border-teal-500/20">
                                                    <span>Terbuka di awal modul</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                    <span>Prasyarat: {{ $prevJudul }}</span>
                                                </span>
                                            @endif

                                            @if(!empty($tipeKasus))
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                                                    <span>Studi kasus: {{ $tipeKasus }}</span>
                                                </span>
                                            @endif

                                            @if($hasVideo)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                    <span>Video</span>
                                                </span>
                                            @endif

                                            @if($hasGambar)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                    <span>Gambar</span>
                                                </span>
                                            @endif

                                            @if($submissions->count() > 0)
                                                <button type="button"
                                                    @click="openSubmissionsModal(@js($judul), @js($submissionsJson))"
                                                    class="inline-flex items-center gap-1.5 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 px-2.5 py-1 text-xs font-medium hover:bg-teal-500/20 transition">
                                                    <span>{{ $submissions->count() }} Pengumpulan Siswa</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Tombol Aksi Lengkap (Read/Review, Urutan, Edit, Hapus) --}}
                                    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                        {{-- Tombol Lihat / Review Materi --}}
                                        <a href="{{ route('guru.materi.show', $item->id) }}"
                                            class="inline-flex items-center gap-1.5 rounded-xl bg-teal-500/10 hover:bg-teal-500/20 text-teal-600 dark:text-teal-400 border border-teal-500/20 px-3.5 py-2 text-xs font-semibold transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Lihat / Review Materi</span>
                                        </a>

                                        {{-- Tombol Panah Atas --}}
                                        <form action="{{ route('guru.materi.urutan', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="direction" value="up">
                                            <button type="submit" @if($isFirst) disabled @endif
                                                title="Pindahkan naik"
                                                class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 transition {{ $isFirst ? 'opacity-40 cursor-not-allowed' : 'hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400' }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Tombol Panah Bawah --}}
                                        <form action="{{ route('guru.materi.urutan', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="direction" value="down">
                                            <button type="submit" @if($isLast) disabled @endif
                                                title="Pindahkan turun"
                                                class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 transition {{ $isLast ? 'opacity-40 cursor-not-allowed' : 'hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400' }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </form>

                                        {{-- Tombol Edit --}}
                                        <button type="button"
                                            @click="openEditModalById({{ $item->id }})"
                                            title="Edit materi"
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button type="button"
                                            @click="openDeleteModal({{ $item->id }}, @js($judul))"
                                            title="Hapus materi"
                                            class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] flex items-center justify-center text-slate-600 dark:text-slate-300 hover:border-red-400 hover:text-red-500 dark:hover:border-red-400/50 dark:hover:text-red-400 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-12 text-center shadow-sm">
                            <p class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">Belum ada sub-bab materi di modul ini</p>
                            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">Klik tombol + Tambah Materi di atas untuk mulai menyusun isi modul ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        @endif

        {{-- =====================================================================
             MODAL TAMBAH / EDIT MODUL PEMBELAJARAN
             ===================================================================== --}}
        <div x-cloak x-show="modulModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 overflow-y-auto">

            <div x-show="modulModalOpen" x-transition.opacity @click="closeModulModal()"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>

            <div x-show="modulModalOpen" x-transition
                class="relative z-10 w-full max-w-xl rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] shadow-2xl overflow-hidden">

                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#071327] flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white"
                        x-text="isEditModul ? 'Edit Modul Pembelajaran' : 'Tambah Modul Pembelajaran'"></h3>
                    <button type="button" @click="closeModulModal()"
                        class="w-9 h-9 rounded-xl bg-slate-200/70 dark:bg-slate-800 text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="modulFormAction" method="POST" class="p-6 space-y-4">
                    @csrf
                    <template x-if="isEditModul">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Judul Modul <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="judul_modul" x-model="modulForm.judul_modul" required
                            placeholder="Contoh: Dasar Pemrograman / Analisis Jaringan"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Kategori / Kelas (Opsional)
                            </label>
                            <input type="text" name="kategori_atau_kelas" x-model="modulForm.kategori_atau_kelas"
                                placeholder="Contoh: Kelas X RPL · Semester 1"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Urutan Modul
                            </label>
                            <input type="number" name="urutan_modul" x-model="modulForm.urutan_modul" min="1" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Deskripsi Singkat Modul
                        </label>
                        <textarea name="deskripsi_singkat" x-model="modulForm.deskripsi_singkat" rows="3"
                            placeholder="Tuliskan ringkasan kompetensi yang dipelajari dalam modul ini..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm leading-relaxed text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 resize-y"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="closeModulModal()"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-teal-600 hover:bg-teal-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition">
                            <span x-text="isEditModul ? 'Simpan Perubahan' : 'Simpan Modul'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =====================================================================
             MODAL KONFIRMASI HAPUS MODUL
             ===================================================================== --}}
        <div x-cloak x-show="deleteModulModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div x-show="deleteModulModalOpen" x-transition.opacity @click="closeDeleteModulModal()"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>

            <div x-show="deleteModulModalOpen" x-transition
                class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl text-center">

                <div class="w-12 h-12 mx-auto rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <h3 class="mt-4 text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                    Hapus Modul Pembelajaran?
                </h3>

                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Apakah Anda yakin ingin menghapus modul
                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="'“' + deleteModulTargetTitle + '”'"></span>?
                    Seluruh sub-bab materi dan studi kasus di dalam modul ini juga akan terhapus.
                </p>

                <form :action="deleteModulAction" method="POST" class="mt-6 grid grid-cols-2 gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button" @click="closeDeleteModulModal()"
                        class="rounded-xl border border-slate-200 dark:border-slate-700 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </button>

                    <button type="submit"
                        class="rounded-xl bg-red-600 hover:bg-red-500 py-2.5 text-sm font-semibold text-white transition">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>

        {{-- =====================================================================
             MODAL TAMBAH / EDIT MATERI DI DALAM MODUL
             ===================================================================== --}}
        <div x-cloak x-show="formModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-5 overflow-y-auto">

            {{-- Backdrop --}}
            <div x-show="formModalOpen" x-transition.opacity @click="closeFormModal()"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>

            {{-- Dialog --}}
            <div x-show="formModalOpen" x-transition
                class="relative z-10 w-full max-w-4xl rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] shadow-2xl my-6 max-h-[92vh] flex flex-col overflow-hidden">

                {{-- Modal Header --}}
                <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#071327] flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white"
                            x-text="isEdit ? 'Edit Materi Pembelajaran' : 'Tambah Materi Pembelajaran'"></h3>
                    </div>
                    <button type="button" @click="closeFormModal()"
                        class="w-9 h-9 rounded-xl bg-slate-200/70 dark:bg-slate-800 text-slate-500 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form :action="formAction" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto p-6 space-y-6">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>
                    <input type="hidden" name="modul_id" :value="form.modul_id">

                    {{-- 1. IDENTITAS MATERI --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 p-5 space-y-4">
                        <h4 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Identitas Materi
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <div class="md:col-span-6">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Judul Materi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="judul_materi" x-model="form.judul_materi" required
                                    placeholder="Contoh: Pengenalan Struktur HTML5"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Bab <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="bab" x-model="form.bab" required
                                    placeholder="Bab 1"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Sub Bab <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="sub_bab" x-model="form.sub_bab" required
                                    placeholder="Sub 1"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Urutan
                                </label>
                                <input type="number" name="urutan" x-model="form.urutan" min="1" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20">
                            </div>
                        </div>
                    </div>

                    {{-- 2. ISI & KONTEN MATERI --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between gap-3">
                            <h4 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                Isi & Konten Materi
                            </h4>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20"
                                x-text="form.blocks.length + ' Bagian'"></span>
                        </div>

                        {{-- Daftar Bagian --}}
                        <div class="space-y-4">
                            <template x-for="(block, bIdx) in form.blocks" :key="block.uid">
                                <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#061024] p-5">

                                    {{-- Toolbar Atas Setiap Bagian --}}
                                    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100 dark:border-slate-800">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-2.5 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 text-xs font-semibold"
                                                x-text="'Bagian ' + (bIdx + 1)"></span>

                                            {{-- Pilihan Tata Letak --}}
                                            <div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-slate-100 dark:bg-slate-900 p-1">
                                                <button type="button" @click="setBlockLayout(bIdx, 'teks_penuh')"
                                                    :class="block.tipe_layout === 'teks_penuh' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-medium transition">
                                                    Teks Penuh
                                                </button>
                                                <button type="button" @click="setBlockLayout(bIdx, 'media_kiri')"
                                                    :class="block.tipe_layout === 'media_kiri' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-medium transition">
                                                    Media Kiri + Teks
                                                </button>
                                                <button type="button" @click="setBlockLayout(bIdx, 'media_kanan')"
                                                    :class="block.tipe_layout === 'media_kanan' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-medium transition">
                                                    Teks + Media Kanan
                                                </button>
                                                <button type="button" @click="setBlockLayout(bIdx, 'media_penuh')"
                                                    :class="block.tipe_layout === 'media_penuh' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                                                    class="px-2.5 py-1 rounded-lg text-xs font-medium transition">
                                                    Media Penuh
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Aksi Urutan & Hapus Bagian --}}
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="moveBlockUp(bIdx)" :disabled="bIdx === 0"
                                                title="Geser bagian ke atas"
                                                class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:text-teal-500 disabled:opacity-30 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="moveBlockDown(bIdx)" :disabled="bIdx === form.blocks.length - 1"
                                                title="Geser bagian ke bawah"
                                                class="w-8 h-8 rounded-lg border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 hover:text-teal-500 disabled:opacity-30 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <button type="button" @click="removeBlock(bIdx)" :disabled="form.blocks.length <= 1"
                                                title="Hapus bagian ini"
                                                class="w-8 h-8 rounded-lg border border-red-500/20 text-red-500 hover:bg-red-500/10 flex items-center justify-center disabled:opacity-30 transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Hidden Inputs untuk Array Blok --}}
                                    <input type="hidden" :name="'blocks[' + bIdx + '][tipe_layout]'" :value="block.tipe_layout">
                                    <input type="hidden" :name="'blocks[' + bIdx + '][tipe_media]'" :value="block.tipe_media">
                                    <input type="hidden" :name="'blocks[' + bIdx + '][existing_gambar]'" :value="block.existing_gambar">

                                    {{-- ISI KONTEN BLOK SESUAI LAYOUT --}}
                                    <div class="grid grid-cols-1 gap-5"
                                        :class="(block.tipe_layout === 'media_kiri' || block.tipe_layout === 'media_kanan') ? 'lg:grid-cols-12 items-start' : ''">

                                        {{-- KOTAK MEDIA --}}
                                        <div x-show="block.tipe_layout !== 'teks_penuh'"
                                            :class="{
                                                'lg:col-span-5 lg:order-1': block.tipe_layout === 'media_kiri',
                                                'lg:col-span-5 lg:order-2': block.tipe_layout === 'media_kanan',
                                                'w-full': block.tipe_layout === 'media_penuh'
                                            }"
                                            class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-4 space-y-3">

                                            <div class="flex items-center justify-between">
                                                <span class="text-xs font-medium text-slate-400">Jenis Media</span>
                                                <div class="inline-flex rounded-lg bg-slate-200/70 dark:bg-slate-800 p-0.5">
                                                    <button type="button" @click="block.tipe_media = 'gambar'"
                                                        :class="block.tipe_media === 'gambar' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                                                        class="px-2.5 py-1 rounded-md text-xs font-medium transition">
                                                        Gambar
                                                    </button>
                                                    <button type="button" @click="block.tipe_media = 'video'"
                                                        :class="block.tipe_media === 'video' ? 'bg-teal-600 text-white shadow-sm' : 'text-slate-500 dark:text-slate-400'"
                                                        class="px-2.5 py-1 rounded-md text-xs font-medium transition">
                                                        Video YouTube
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Input Gambar --}}
                                            <div x-show="block.tipe_media === 'gambar'" class="space-y-3">
                                                <template x-if="block.preview_url || block.gambar_url">
                                                    <div class="relative rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-950/30">
                                                        <img :src="block.preview_url || block.gambar_url" class="w-full max-h-48 object-contain mx-auto">
                                                    </div>
                                                </template>

                                                <label class="flex flex-col items-center justify-center rounded-xl border border-dashed border-teal-500/30 bg-white dark:bg-slate-900 px-4 py-4 text-center cursor-pointer hover:border-teal-500 transition">
                                                    <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 flex items-center justify-center mb-1.5">
                                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                        </svg>
                                                    </div>
                                                    <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">
                                                        Pilih File Gambar
                                                    </span>
                                                    <span class="text-xs font-medium text-slate-400 mt-0.5">JPG, PNG, WEBP (Maks 5MB)</span>
                                                    <input type="file" :name="'block_images[' + bIdx + ']'" accept="image/*"
                                                        @change="onBlockImageChange($event, bIdx)" class="hidden">
                                                </label>
                                            </div>

                                            {{-- Input Video YouTube --}}
                                            <div x-show="block.tipe_media === 'video'" class="space-y-2">
                                                <label class="block text-xs font-medium text-slate-400">
                                                    URL Video YouTube
                                                </label>
                                                <input type="text" :name="'blocks[' + bIdx + '][url_video]'" x-model="block.url_video"
                                                    placeholder="https://www.youtube.com/watch?v=..."
                                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-950 px-3.5 py-2 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500">
                                            </div>
                                        </div>

                                        {{-- KOTAK TEKS PENJELASAN --}}
                                        <div :class="{
                                                'lg:col-span-7 lg:order-2': block.tipe_layout === 'media_kiri',
                                                'lg:col-span-7 lg:order-1': block.tipe_layout === 'media_kanan',
                                                'w-full': block.tipe_layout === 'teks_penuh' || block.tipe_layout === 'media_penuh'
                                            }"
                                            class="space-y-3">
                                            <div>
                                                <label class="block text-xs font-medium text-slate-400 mb-1"
                                                    x-text="block.tipe_layout === 'media_penuh' ? 'Judul Media (Opsional)' : 'Sub-Judul Bagian'"></label>
                                                <input type="text" :name="'blocks[' + bIdx + '][sub_judul]'" x-model="block.sub_judul"
                                                    :placeholder="block.tipe_layout === 'media_penuh' ? 'Contoh: Diagram Struktur DOM' : 'Contoh: Struktur Dasar Dokumen HTML'"
                                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-medium text-slate-400 mb-1"
                                                    x-text="block.tipe_layout === 'media_penuh' ? 'Keterangan Media' : 'Uraian Materi'"></label>
                                                <textarea :name="'blocks[' + bIdx + '][isi_teks]'" x-model="block.isi_teks"
                                                    :rows="block.tipe_layout === 'media_penuh' ? 2 : 5"
                                                    :placeholder="block.tipe_layout === 'media_penuh' ? 'Tuliskan keterangan gambar atau video...' : 'Tuliskan penjelasan materi...'"
                                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm leading-relaxed text-slate-800 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:border-teal-500 resize-y"></textarea>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Tombol Tambah Bagian Baru --}}
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 p-4 flex flex-wrap items-center justify-between gap-3">
                            <span class="text-xs font-medium text-slate-400">
                                Tambah Bagian Materi:
                            </span>
                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" @click="addBlock('teks_penuh')"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                    <span>+ Teks Penuh</span>
                                </button>
                                <button type="button" @click="addBlock('media_kiri')"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                    <span>+ Media & Teks</span>
                                </button>
                                <button type="button" @click="addBlock('media_penuh')"
                                    class="inline-flex items-center gap-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                    <span>+ Media Penuh</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- 3. STUDI KASUS --}}
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/40 p-5 space-y-4">
                        <h4 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Studi Kasus Materi
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Judul Studi Kasus
                                </label>
                                <input type="text" name="judul_studi_kasus" x-model="form.judul_studi_kasus"
                                    placeholder="Contoh: Implementasi Struktur HTML"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Tipe Studi Kasus
                                </label>
                                <select name="tipe_studi_kasus" x-model="form.tipe_studi_kasus"
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                                    <option value="Esai singkat">Esai singkat</option>
                                    <option value="Pilihan ganda">Pilihan ganda</option>
                                    <option value="Kode program">Kode program</option>
                                    <option value="">Tidak ada studi kasus</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="form.tipe_studi_kasus !== ''" x-transition class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                    Instruksi / Soal Studi Kasus
                                </label>
                                <textarea name="soal_studi_kasus" x-model="form.soal_studi_kasus" rows="3"
                                    placeholder="Tuliskan skenario masalah atau instruksi pengerjaan studi kasus..."
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-4 py-2.5 text-sm leading-relaxed text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-teal-500 resize-y"></textarea>
                            </div>

                            <div x-show="form.tipe_studi_kasus === 'Pilihan ganda'" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <input type="text" name="opsi_a" x-model="form.opsi_a" placeholder="Opsi A"
                                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white">
                                <input type="text" name="opsi_b" x-model="form.opsi_b" placeholder="Opsi B"
                                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white">
                                <input type="text" name="opsi_c" x-model="form.opsi_c" placeholder="Opsi C"
                                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white">
                                <input type="text" name="opsi_d" x-model="form.opsi_d" placeholder="Opsi D"
                                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white">
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Submit Modal --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="closeFormModal()"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-teal-600 hover:bg-teal-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition">
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Materi'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DAFTAR PENGUMPULAN & LAMPIRAN FILE SISWA --}}
        <div x-cloak x-show="submissionsModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div x-show="submissionsModalOpen" x-transition.opacity @click="submissionsModalOpen = false"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>

            <div x-show="submissionsModalOpen" x-transition
                class="relative z-10 w-full max-w-3xl rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl max-h-[85vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <p class="text-xs font-medium text-slate-400">Pengumpulan Studi Kasus</p>
                        <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mt-0.5"
                            x-text="submissionsModalTitle"></h3>
                    </div>
                    <button type="button" @click="submissionsModalOpen = false"
                        class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <template x-for="sub in activeSubmissions" :key="sub.id">
                        <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-4 space-y-2.5">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800 dark:text-white" x-text="sub.siswa_nama"></p>
                                    <p class="text-xs font-medium text-slate-400" x-text="'NIS: ' + sub.siswa_nis + ' • Dikumpulkan ' + sub.tanggal"></p>
                                </div>
                                <template x-if="sub.file_url">
                                    <a :href="sub.file_url" target="_blank" download
                                        class="inline-flex items-center gap-1.5 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 px-3 py-1.5 text-xs font-semibold hover:bg-teal-500/20 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        <span x-text="sub.nama_file_asli || 'Unduh Lampiran'"></span>
                                    </a>
                                </template>
                            </div>
                            <div class="rounded-xl bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-3 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line font-mono"
                                x-text="sub.jawaban_teks"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- MODAL KONFIRMASI HAPUS MATERI --}}
        <div x-cloak x-show="deleteModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div x-show="deleteModalOpen" x-transition.opacity @click="closeDeleteModal()"
                class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm"></div>

            <div x-show="deleteModalOpen" x-transition
                class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl text-center">

                <div class="w-12 h-12 mx-auto rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 flex items-center justify-center">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <h3 class="mt-4 text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                    Hapus Materi?
                </h3>

                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Apakah Anda yakin ingin menghapus materi
                    <span class="font-semibold text-slate-800 dark:text-slate-200" x-text="'“' + deleteTargetTitle + '”'"></span>?
                    Urutan materi di dalam modul ini akan disesuaikan secara otomatis.
                </p>

                <form :action="deleteAction" method="POST" class="mt-6 grid grid-cols-2 gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button" @click="closeDeleteModal()"
                        class="rounded-xl border border-slate-200 dark:border-slate-700 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        Batal
                    </button>

                    <button type="submit"
                        class="rounded-xl bg-red-600 hover:bg-red-500 py-2.5 text-sm font-semibold text-white transition">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script>
        function kelolaMateriPage(config) {
            let blockCounter = 1;
            const makeUid = () => 'blk_' + Date.now() + '_' + (blockCounter++);

            return {
                // State Modul
                modulModalOpen: false,
                deleteModulModalOpen: false,
                isEditModul: false,
                modulFormAction: config.modulStoreUrl,
                deleteModulAction: '',
                deleteModulTargetTitle: '',
                modulMap: config.modulMap || {},
                modulForm: {
                    judul_modul: '',
                    kategori_atau_kelas: '',
                    deskripsi_singkat: '',
                    urutan_modul: config.nextModulUrutan || 1
                },

                // State Materi
                formModalOpen: false,
                deleteModalOpen: false,
                submissionsModalOpen: false,
                submissionsModalTitle: '',
                activeSubmissions: [],
                isEdit: false,
                formAction: config.storeUrl,
                deleteAction: '',
                deleteTargetTitle: '',
                materiMap: config.materiMap || {},
                form: {
                    modul_id: config.activeModulId || '',
                    judul_materi: '',
                    bab: 'Bab 1',
                    sub_bab: 'Sub 1',
                    urutan: config.nextUrutan || 1,
                    judul_studi_kasus: '',
                    tipe_studi_kasus: 'Esai singkat',
                    soal_studi_kasus: '',
                    opsi_a: '',
                    opsi_b: '',
                    opsi_c: '',
                    opsi_d: '',
                    blocks: []
                },

                initPage() {
                    if (config.autoEditId && this.materiMap[config.autoEditId]) {
                        this.openEditModal(this.materiMap[config.autoEditId]);
                    }
                },

                // Fungsi Modal Modul
                openCreateModulModal() {
                    this.isEditModul = false;
                    this.modulFormAction = config.modulStoreUrl;
                    this.modulForm = {
                        judul_modul: '',
                        kategori_atau_kelas: '',
                        deskripsi_singkat: '',
                        urutan_modul: config.nextModulUrutan || 1
                    };
                    this.modulModalOpen = true;
                },

                openEditModulModalById(id) {
                    const mod = this.modulMap[id];
                    if (!mod) return;
                    this.isEditModul = true;
                    this.modulFormAction = config.modulUpdateBaseUrl + '/' + mod.id;
                    this.modulForm = {
                        judul_modul: mod.judul_modul || '',
                        kategori_atau_kelas: mod.kategori_atau_kelas || '',
                        deskripsi_singkat: mod.deskripsi_singkat || '',
                        urutan_modul: mod.urutan_modul || 1
                    };
                    this.modulModalOpen = true;
                },

                closeModulModal() {
                    this.modulModalOpen = false;
                },

                openDeleteModulModal(id, title) {
                    this.deleteModulAction = config.modulDeleteBaseUrl + '/' + id;
                    this.deleteModulTargetTitle = title;
                    this.deleteModulModalOpen = true;
                },

                closeDeleteModulModal() {
                    this.deleteModulModalOpen = false;
                },

                // Fungsi Modal Materi
                openEditModalById(id) {
                    if (this.materiMap[id]) {
                        this.openEditModal(this.materiMap[id]);
                    }
                },

                createDefaultBlock(layout = 'teks_penuh') {
                    return {
                        uid: makeUid(),
                        tipe_layout: layout,
                        sub_judul: '',
                        isi_teks: '',
                        tipe_media: layout === 'teks_penuh' ? 'none' : 'gambar',
                        existing_gambar: '',
                        gambar_url: '',
                        preview_url: '',
                        url_video: ''
                    };
                },

                setBlockLayout(idx, layout) {
                    const blk = this.form.blocks[idx];
                    if (!blk) return;
                    blk.tipe_layout = layout;
                    if (layout === 'teks_penuh') {
                        blk.tipe_media = 'none';
                    } else if (blk.tipe_media === 'none') {
                        blk.tipe_media = blk.url_video ? 'video' : 'gambar';
                    }
                },

                addBlock(layout = 'teks_penuh') {
                    this.form.blocks.push(this.createDefaultBlock(layout));
                },

                removeBlock(idx) {
                    if (this.form.blocks.length <= 1) return;
                    this.form.blocks.splice(idx, 1);
                },

                moveBlockUp(idx) {
                    if (idx <= 0) return;
                    const temp = this.form.blocks[idx];
                    this.form.blocks.splice(idx, 1);
                    this.form.blocks.splice(idx - 1, 0, temp);
                },

                moveBlockDown(idx) {
                    if (idx >= this.form.blocks.length - 1) return;
                    const temp = this.form.blocks[idx];
                    this.form.blocks.splice(idx, 1);
                    this.form.blocks.splice(idx + 1, 0, temp);
                },

                onBlockImageChange(event, idx) {
                    const file = event.target.files[0];
                    if (!file || !this.form.blocks[idx]) return;
                    this.form.blocks[idx].preview_url = URL.createObjectURL(file);
                    this.form.blocks[idx].tipe_media = 'gambar';
                },

                openCreateModal() {
                    this.isEdit = false;
                    this.formAction = config.storeUrl;
                    this.form = {
                        modul_id: config.activeModulId || '',
                        judul_materi: '',
                        bab: 'Bab 1',
                        sub_bab: 'Sub ' + (config.nextUrutan || 1),
                        urutan: config.nextUrutan || 1,
                        judul_studi_kasus: '',
                        tipe_studi_kasus: 'Esai singkat',
                        soal_studi_kasus: '',
                        opsi_a: '',
                        opsi_b: '',
                        opsi_c: '',
                        opsi_d: '',
                        blocks: [this.createDefaultBlock('teks_penuh')]
                    };
                    this.formModalOpen = true;
                },

                openEditModal(item) {
                    this.isEdit = true;
                    this.formAction = config.updateBaseUrl + '/' + item.id;

                    let loadedBlocks = [];
                    if (Array.isArray(item.blocks) && item.blocks.length > 0) {
                        loadedBlocks = item.blocks.map(b => ({
                            uid: makeUid(),
                            tipe_layout: b.tipe_layout || 'teks_penuh',
                            sub_judul: b.sub_judul || '',
                            isi_teks: b.isi_teks || '',
                            tipe_media: b.tipe_media || 'none',
                            existing_gambar: b.existing_gambar || '',
                            gambar_url: b.gambar_url || '',
                            preview_url: '',
                            url_video: b.url_video || ''
                        }));
                    } else {
                        loadedBlocks = [{
                            uid: makeUid(),
                            tipe_layout: item.has_video ? 'media_kiri' : 'teks_penuh',
                            sub_judul: item.judul_materi || '',
                            isi_teks: item.konten_materi || '',
                            tipe_media: item.has_video ? 'video' : 'none',
                            existing_gambar: '',
                            gambar_url: '',
                            preview_url: '',
                            url_video: item.video_url || ''
                        }];
                    }

                    this.form = {
                        modul_id: item.modul_id || config.activeModulId || '',
                        judul_materi: item.judul_materi || '',
                        bab: item.bab || 'Bab 1',
                        sub_bab: item.sub_bab || 'Sub 1',
                        urutan: item.urutan || 1,
                        judul_studi_kasus: item.judul_studi_kasus || ('Studi Kasus: ' + (item.judul_materi || '')),
                        tipe_studi_kasus: item.tipe_studi_kasus ?? '',
                        soal_studi_kasus: item.soal_studi_kasus || '',
                        opsi_a: item.opsi_a || '',
                        opsi_b: item.opsi_b || '',
                        opsi_c: item.opsi_c || '',
                        opsi_d: item.opsi_d || '',
                        blocks: loadedBlocks
                    };
                    this.formModalOpen = true;
                },

                closeFormModal() {
                    this.formModalOpen = false;
                },

                openSubmissionsModal(judul, list) {
                    this.submissionsModalTitle = judul;
                    this.activeSubmissions = list || [];
                    this.submissionsModalOpen = true;
                },

                openDeleteModal(id, title) {
                    this.deleteAction = config.deleteBaseUrl + '/' + id;
                    this.deleteTargetTitle = title;
                    this.deleteModalOpen = true;
                },

                closeDeleteModal() {
                    this.deleteModalOpen = false;
                }
            };
        }
    </script>

</x-layouts.guru>
