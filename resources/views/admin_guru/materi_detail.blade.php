<x-layouts.guru :title="'Review Materi: ' . ($materi->judul_materi ?: $materi->judul)">

    @php
        $judulMateri = $materi->judul_materi ?: $materi->judul;
        $namaModul = $materi->modul?->judul_modul ?? 'Modul Pembelajaran';
        $kasusTipe = $studiKasus?->tipe_studi_kasus ?? ($materi->tipe_studi_kasus ?: '');
        $kasusJudul = $studiKasus?->judul_studi_kasus ?? ('Studi Kasus: ' . $judulMateri);
        $kasusInstruksi = $studiKasus?->instruksi_soal ?? ($materi->soal_studi_kasus ?: '');
        $opsiA = $studiKasus?->opsi_a ?? ($materi->opsi_a ?: '');
        $opsiB = $studiKasus?->opsi_b ?? ($materi->opsi_b ?: '');
        $opsiC = $studiKasus?->opsi_c ?? ($materi->opsi_c ?: '');
        $opsiD = $studiKasus?->opsi_d ?? ($materi->opsi_d ?: '');
        $submissions = $studiKasus ? $studiKasus->pengumpulan : collect();
    @endphp

    <div x-data="{
        lightboxOpen: false,
        lightboxUrl: '',
        lightboxCaption: '',
        openLightbox(url, caption) {
            this.lightboxUrl = url;
            this.lightboxCaption = caption || '';
            this.lightboxOpen = true;
        }
    }" class="max-w-4xl mx-auto pb-16">

        {{-- HEADER KONTROL GURU (FLOATING BAR) --}}
        <div class="sticky top-20 z-20 -mx-4 px-4 lg:-mx-6 lg:px-6 py-3 bg-white/95 dark:bg-[#071327]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 mb-6">
            <div class="max-w-4xl mx-auto flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <a href="{{ route('guru.materi', ['modul_id' => $materi->modul_id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500/50 hover:text-teal-600 dark:hover:text-teal-400 transition flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>← Kembali ke Modul</span>
                    </a>
                    <span class="hidden sm:inline-block text-xs font-medium text-slate-400 truncate">
                        {{ $namaModul }} · Pratinjau Baca Siswa
                    </span>
                </div>

                <div class="flex items-center gap-2.5 flex-shrink-0">
                    <a href="{{ route('guru.materi', ['modul_id' => $materi->modul_id, 'edit' => $materi->id]) }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-4 py-2 text-xs font-semibold text-white shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <span>Edit Materi Ini</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- ARTIKEL MATERI UTAMA (100% IDENTIK DENGAN HALAMAN BACA SISWA) --}}
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] px-6 py-8 sm:px-10 sm:py-10 lg:px-12 lg:py-12 shadow-sm">

            {{-- Header Artikel Materi --}}
            <header class="pb-8 border-b border-slate-200 dark:border-slate-800">
                <div class="flex flex-wrap items-center gap-2.5 mb-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
                        {{ $namaModul }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium">
                        {{ $materi->bab ?: 'Bab 1' }} · {{ $materi->sub_bab ?: 'Sub 1' }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">
                        Urutan #{{ $materi->urutan }}
                    </span>
                </div>

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                    {{ $judulMateri }}
                </h1>

                <div class="mt-4 flex flex-wrap items-center gap-3 text-xs font-medium text-slate-400">
                    <span>Disusun oleh <strong class="text-slate-700 dark:text-slate-200">{{ $materi->guru->name ?? 'Guru Pengampu' }}</strong></span>
                    <span>•</span>
                    <span>{{ $materi->bagianMateri->count() }} Bagian Pembahasan</span>
                </div>
            </header>

            {{-- ISI BAGIAN-BAGIAN MATERI --}}
            <div class="mt-10 space-y-12">
                @foreach($materi->bagianMateri as $bIdx => $block)
                    @php
                        $layout = $block->tipe_layout ?: 'teks_penuh';
                        $tipeMedia = $block->tipe_media ?: 'none';
                        $gambarUrl = $block->gambar_url;
                        $embedUrl = $block->youtube_embed_url;
                        $watchUrl = $block->youtube_watch_url;
                    @endphp

                    <section class="scroll-mt-28">

                        {{-- 1. TATA LETAK: TEKS PENUH --}}
                        @if($layout === 'teks_penuh' || $tipeMedia === 'none')
                            <div class="space-y-3">
                                @if(!empty($block->sub_judul))
                                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                        {{ $block->sub_judul }}
                                    </h2>
                                @endif
                                @if(!empty($block->isi_teks))
                                    <div class="text-sm leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line">
                                        {{ $block->isi_teks }}
                                    </div>
                                @endif
                            </div>

                        {{-- 2. TATA LETAK: 2 KOLOM BERDAMPINGAN (MEDIA KIRI ATAU MEDIA KANAN) --}}
                        @elseif($layout === 'media_kiri' || $layout === 'media_kanan')
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                                {{-- Kolom Media --}}
                                <div class="lg:col-span-6 {{ $layout === 'media_kanan' ? 'lg:order-2' : 'lg:order-1' }}">
                                    @if($tipeMedia === 'gambar' && $gambarUrl)
                                        <figure class="group relative">
                                            <div @click="openLightbox(@js($gambarUrl), @js($block->sub_judul ?: $judulMateri))"
                                                class="relative overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-900 cursor-zoom-in">
                                                <img src="{{ $gambarUrl }}" alt="{{ $block->sub_judul ?: $judulMateri }}"
                                                    class="w-full h-auto object-cover group-hover:scale-[1.02] transition-transform duration-300">
                                                <div class="absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-lg bg-slate-950/75 px-3 py-1.5 text-xs font-medium text-white">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                    </svg>
                                                    <span>Klik untuk perbesar</span>
                                                </div>
                                            </div>
                                        </figure>
                                    @elseif($tipeMedia === 'video' && !empty($block->url_video))
                                        <div class="space-y-3">
                                            @if($embedUrl)
                                                <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-950 shadow-sm">
                                                    <iframe src="{{ $embedUrl }}"
                                                        title="{{ $block->sub_judul ?: $judulMateri }}"
                                                        class="w-full h-full"
                                                        frameborder="0"
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                        allowfullscreen></iframe>
                                                </div>
                                            @endif
                                            @if($watchUrl)
                                                <div class="flex items-center justify-between pt-0.5">
                                                    <span class="text-xs font-medium text-slate-400">Video Pembelajaran</span>
                                                    <a href="{{ $watchUrl }}" target="_blank" rel="noopener noreferrer"
                                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                                                        <span>Tonton langsung di YouTube ↗</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>

                                {{-- Kolom Teks Penjelasan --}}
                                <div class="lg:col-span-6 {{ $layout === 'media_kanan' ? 'lg:order-1' : 'lg:order-2' }} space-y-3">
                                    @if(!empty($block->sub_judul))
                                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                            {{ $block->sub_judul }}
                                        </h2>
                                    @endif
                                    @if(!empty($block->isi_teks))
                                        <div class="text-sm leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line">
                                            {{ $block->isi_teks }}
                                        </div>
                                    @endif
                                </div>

                            </div>

                        {{-- 3. TATA LETAK: GAMBAR / VIDEO PENUH BESERTA CAPTION --}}
                        @elseif($layout === 'media_penuh')
                            <div class="space-y-4">
                                @if(!empty($block->sub_judul))
                                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                        {{ $block->sub_judul }}
                                    </h2>
                                @endif

                                @if($tipeMedia === 'gambar' && $gambarUrl)
                                    <figure class="group">
                                        <div @click="openLightbox(@js($gambarUrl), @js($block->sub_judul ?: ($block->isi_teks ?: $judulMateri)))"
                                            class="relative overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-900 cursor-zoom-in">
                                            <img src="{{ $gambarUrl }}" alt="{{ $block->sub_judul ?: $judulMateri }}"
                                                class="w-full max-h-[520px] object-contain mx-auto group-hover:scale-[1.01] transition-transform duration-300">
                                            <div class="absolute bottom-3 right-3 inline-flex items-center gap-1.5 rounded-lg bg-slate-950/75 px-3 py-1.5 text-xs font-medium text-white">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                                <span>Klik untuk memperbesar gambar</span>
                                            </div>
                                        </div>
                                        @if(!empty($block->isi_teks))
                                            <figcaption class="mt-3 text-xs font-medium text-slate-400 text-center">
                                                {{ $block->isi_teks }}
                                            </figcaption>
                                        @endif
                                    </figure>
                                @elseif($tipeMedia === 'video' && !empty($block->url_video))
                                    <div class="space-y-3">
                                        @if($embedUrl)
                                            <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-950 shadow-sm">
                                                <iframe src="{{ $embedUrl }}"
                                                    title="{{ $block->sub_judul ?: $judulMateri }}"
                                                    class="w-full h-full"
                                                    frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                                    allowfullscreen></iframe>
                                            </div>
                                        @endif
                                        <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                                            @if(!empty($block->isi_teks))
                                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                                    {{ $block->isi_teks }}
                                                </p>
                                            @else
                                                <span class="text-xs font-medium text-slate-400">Video Materi</span>
                                            @endif
                                            @if($watchUrl)
                                                <a href="{{ $watchUrl }}" target="_blank" rel="noopener noreferrer"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                                                    <span>Tonton langsung di YouTube ↗</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                    </section>
                @endforeach
            </div>

        </article>

        {{-- DETAIL INSTRUKSI STUDI KASUS DI BAGIAN BAWAH --}}
        <section class="mt-8 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 sm:p-8 lg:p-10 shadow-sm space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-slate-200 dark:border-slate-800">
                <div>
                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                        Evaluasi Praktik · {{ $kasusTipe ?: 'Studi Kasus' }}
                    </span>
                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mt-0.5">
                        {{ $kasusJudul }}
                    </h2>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
                    {{ $submissions->count() }} Siswa Mengumpulkan
                </span>
            </div>

            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-5">
                <p class="text-xs font-medium text-slate-400 mb-2">Instruksi Studi Kasus</p>
                <div class="text-sm leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line">
                    {{ $kasusInstruksi ?: 'Belum ada instruksi studi kasus yang ditambahkan.' }}
                </div>
            </div>

            @if($kasusTipe === 'Pilihan ganda' && !empty($opsiA))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach(['A' => $opsiA, 'B' => $opsiB, 'C' => $opsiC, 'D' => $opsiD] as $key => $label)
                        @if(!empty($label))
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 p-3.5 text-sm text-slate-700 dark:text-slate-200">
                                <span class="font-semibold text-teal-600 dark:text-teal-400 mr-1.5">{{ $key }}.</span>
                                <span>{{ $label }}</span>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            @if($submissions->count() > 0)
                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                    <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                        Daftar Pengumpulan Siswa
                    </h3>
                    <div class="space-y-3">
                        @foreach($submissions as $sub)
                            <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/50 p-4 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ $sub->siswa->name ?? 'Siswa' }}</p>
                                        <p class="text-xs font-medium text-slate-400">
                                            NIS: {{ $sub->siswa->nomor_induk ?? '-' }} · {{ $sub->tanggal_kumpul?->translatedFormat('d M Y, H:i') }}
                                        </p>
                                    </div>
                                    @if($sub->file_url)
                                        <a href="{{ $sub->file_url }}" target="_blank" download
                                            class="inline-flex items-center gap-1.5 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 px-3 py-1.5 text-xs font-semibold hover:bg-teal-500/20 transition">
                                            <span>Unduh Lampiran ({{ $sub->nama_file_asli ?: basename($sub->file_lampiran) }})</span>
                                        </a>
                                    @endif
                                </div>
                                <div class="rounded-lg bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 p-3 text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line font-mono">{{ $sub->jawaban_teks }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        {{-- NAVIGASI ANTAR MATERI BAWAH (DI DALAM MODUL YANG SAMA) --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            @if($prevMateri)
                <a href="{{ route('guru.materi.show', $prevMateri->id) }}"
                    class="w-full sm:w-auto inline-flex items-center gap-3 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] px-5 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500/40 transition">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>Sebelumnya: {{ $prevMateri->judul_materi ?: $prevMateri->judul }}</span>
                </a>
            @else
                <div></div>
            @endif

            @if($nextMateri)
                <a href="{{ route('guru.materi.show', $nextMateri->id) }}"
                    class="w-full sm:w-auto inline-flex items-center justify-end gap-3 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition">
                    <span>Materi Berikutnya: {{ $nextMateri->judul_materi ?: $nextMateri->judul }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            @endif
        </div>

        {{-- IMAGE LIGHTBOX / PREVIEW MODAL (CLICK-TO-ZOOM) --}}
        <div x-cloak x-show="lightboxOpen"
            @keydown.escape.window="lightboxOpen = false"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 backdrop-blur-md p-4"
            x-transition.opacity>

            <button type="button" @click="lightboxOpen = false"
                class="fixed top-5 right-5 z-10 w-10 h-10 rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/15 flex items-center justify-center transition">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div @click.away="lightboxOpen = false" class="max-w-5xl max-h-[90vh] flex flex-col items-center">
                <img :src="lightboxUrl" :alt="lightboxCaption"
                    class="max-w-full max-h-[82vh] object-contain rounded-xl shadow-2xl border border-white/10">
                <p x-show="lightboxCaption" x-text="lightboxCaption"
                    class="mt-3 text-sm text-slate-300 text-center max-w-2xl"></p>
            </div>
        </div>

    </div>

</x-layouts.guru>
