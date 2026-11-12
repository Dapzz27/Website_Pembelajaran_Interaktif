<x-layouts.siswa :title="($materi->judul_materi ?: $materi->judul)">

    @php
        $judulMateri = $materi->judul_materi ?: $materi->judul;
        $celebration = session('celebration');

        $requiredTrackingKeys = [];
        foreach ($materi->bagianMateri as $b) {
            $requiredTrackingKeys[] = 'block_' . $b->id;
            if ($b->tipe_media === 'gambar' && !empty($b->file_gambar)) {
                $requiredTrackingKeys[] = 'media_' . $b->id;
            } elseif ($b->tipe_media === 'video' && !empty($b->url_video)) {
                $requiredTrackingKeys[] = 'media_' . $b->id;
            }
        }

        $initialCompletedKeys = array_values(array_intersect($requiredTrackingKeys, $terbacaList ?? []));
        $isAlreadyRead = (bool) $materi->baca_selesai || ($logBaca && $logBaca->status_baca === 'selesai');
        if ($isAlreadyRead) {
            $initialCompletedKeys = $requiredTrackingKeys;
        }

        $kasusTipe = $studiKasus?->tipe_studi_kasus ?? ($materi->tipe_studi_kasus ?: '');
        $kasusJudul = $studiKasus?->judul_studi_kasus ?? ('Studi Kasus: ' . $judulMateri);
        $kasusInstruksi = $studiKasus?->instruksi_soal ?? ($materi->soal_studi_kasus ?: '');
        $opsiA = $studiKasus?->opsi_a ?? ($materi->opsi_a ?: '');
        $opsiB = $studiKasus?->opsi_b ?? ($materi->opsi_b ?: '');
        $opsiC = $studiKasus?->opsi_c ?? ($materi->opsi_c ?: '');
        $opsiD = $studiKasus?->opsi_d ?? ($materi->opsi_d ?: '');
    @endphp

    <div x-data="materiReaderApp({
        materiId: {{ $materi->id }},
        trackUrl: '{{ route('siswa.materi.track_baca', $materi->id) }}',
        csrfToken: '{{ csrf_token() }}',
        requiredKeys: {{ Js::from($requiredTrackingKeys) }},
        completedKeys: {{ Js::from($initialCompletedKeys) }},
        bacaSelesai: {{ $isAlreadyRead ? 'true' : 'false' }},
        hasCelebration: {{ $celebration ? 'true' : 'false' }},
        initialJawaban: {{ Js::from($pengumpulan?->jawaban_teks ?? ($materi->jawaban_siswa ?: '')) }}
    })" x-init="initTracker()" class="max-w-4xl mx-auto pb-16">

        {{-- STICKY READING PROGRESS BAR --}}
        <div class="sticky top-20 z-20 -mx-4 px-4 lg:-mx-6 lg:px-6 py-2.5 bg-white/95 dark:bg-[#071327]/95 backdrop-blur-md border-b border-slate-200 dark:border-slate-800 mb-6 transition-all">
            <div class="max-w-4xl mx-auto flex flex-col gap-1.5">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <a href="{{ route('siswa.materi', ['modul_id' => $materi->modul_id]) }}"
                            class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400 hover:text-teal-600 dark:hover:text-teal-400 transition flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>{{ $materi->modul?->judul_modul ?: 'Daftar Materi' }}</span>
                        </a>
                        <span class="text-slate-300 dark:text-slate-700">/</span>
                        <span class="text-xs font-medium text-slate-600 dark:text-slate-300 truncate">{{ $judulMateri }}</span>
                    </div>

                    <div class="flex items-center gap-2.5 flex-shrink-0">
                        <template x-if="bacaSelesai">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                                <span>Selesai Dibaca</span>
                            </span>
                        </template>
                        <template x-if="!bacaSelesai">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-teal-600 dark:text-teal-400">
                                <span x-text="'Progres Baca: ' + progressPct() + '%'"></span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Thin Solid Teal Progress Bar --}}
                <div class="h-1 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                    <div class="h-full rounded-full bg-teal-600 transition-all duration-500 ease-out"
                        :style="'width: ' + progressPct() + '%'"></div>
                </div>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition
                class="mb-6 flex items-center justify-between gap-3 rounded-xl border border-teal-500/25 bg-teal-500/10 px-5 py-3.5 text-sm font-medium text-teal-700 dark:text-teal-300">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="p-1 text-teal-600 dark:text-teal-300">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="mb-6 rounded-xl border border-red-500/25 bg-red-500/10 px-5 py-4 text-sm text-red-700 dark:text-red-300">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ARTIKEL MATERI UTAMA (CLEAN & SEAMLESS) --}}
        <article class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] px-6 py-8 sm:px-10 sm:py-10 lg:px-12 lg:py-12 shadow-sm">

            {{-- Header Artikel Materi --}}
            <header class="pb-8 border-b border-slate-200 dark:border-slate-800">
                <div class="flex flex-wrap items-center gap-2.5 mb-3">
                    @if($materi->modul)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 text-xs font-medium">
                            {{ $materi->modul->judul_modul }}
                        </span>
                    @endif
                    <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
                        {{ $materi->bab ?: 'Bab 1' }} · {{ $materi->sub_bab ?: 'Sub 1' }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">
                        Urutan #{{ $materi->urutan }}
                    </span>
                    @if($materi->lock_state === 'selesai')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 text-xs font-medium">
                            Selesai
                        </span>
                    @endif
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
                        $blockKey = 'block_' . $block->id;
                        $mediaKey = 'media_' . $block->id;
                        $layout = $block->tipe_layout ?: 'teks_penuh';
                        $tipeMedia = $block->tipe_media ?: 'none';
                        $gambarUrl = $block->gambar_url;
                        $embedUrl = $block->youtube_embed_url;
                        $watchUrl = $block->youtube_watch_url;
                    @endphp

                    <section data-track-key="{{ $blockKey }}" class="scroll-mt-28">

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

                        {{-- 2. TATA LETAK: 2 KOLOM BERDAMPINGAN --}}
                        @elseif($layout === 'media_kiri' || $layout === 'media_kanan')
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                                {{-- Kolom Media --}}
                                <div class="lg:col-span-6 {{ $layout === 'media_kanan' ? 'lg:order-2' : 'lg:order-1' }}">
                                    @if($tipeMedia === 'gambar' && $gambarUrl)
                                        <figure class="group relative">
                                            <div @click="openLightbox(@js($gambarUrl), @js($block->sub_judul ?: $judulMateri), '{{ $mediaKey }}')"
                                                class="relative overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-900 cursor-zoom-in">
                                                <img src="{{ $gambarUrl }}" alt="{{ $block->sub_judul ?: $judulMateri }}"
                                                    @load="markMediaSeen('{{ $mediaKey }}')"
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
                                        <div class="space-y-3"
                                            @mouseenter="markMediaSeen('{{ $mediaKey }}')"
                                            @click="markMediaSeen('{{ $mediaKey }}')">
                                            @if($embedUrl)
                                                <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-950 shadow-sm"
                                                    data-track-media="{{ $mediaKey }}">
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
                                                        @click="markMediaSeen('{{ $mediaKey }}')"
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

                        {{-- 3. TATA LETAK: MEDIA PENUH --}}
                        @elseif($layout === 'media_penuh')
                            <div class="space-y-4">
                                @if(!empty($block->sub_judul))
                                    <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                                        {{ $block->sub_judul }}
                                    </h2>
                                @endif

                                @if($tipeMedia === 'gambar' && $gambarUrl)
                                    <figure class="group">
                                        <div @click="openLightbox(@js($gambarUrl), @js($block->sub_judul ?: ($block->isi_teks ?: $judulMateri)), '{{ $mediaKey }}')"
                                            class="relative overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-900 cursor-zoom-in">
                                            <img src="{{ $gambarUrl }}" alt="{{ $block->sub_judul ?: $judulMateri }}"
                                                @load="markMediaSeen('{{ $mediaKey }}')"
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
                                    <div class="space-y-3"
                                        @mouseenter="markMediaSeen('{{ $mediaKey }}')"
                                        @click="markMediaSeen('{{ $mediaKey }}')">
                                        @if($embedUrl)
                                            <div class="aspect-video w-full rounded-xl overflow-hidden bg-slate-950 shadow-sm"
                                                data-track-media="{{ $mediaKey }}">
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
                                                    @click="markMediaSeen('{{ $mediaKey }}')"
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

            {{-- Penanda Akhir Artikel untuk Deteksi Scroll Penuh --}}
            <div id="akhir-bacaan-sentinel" class="mt-10 pt-6 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3">
                <span class="text-xs font-medium text-slate-400"
                    x-text="bacaSelesai
                        ? 'Seluruh bagian materi telah selesai dibaca.'
                        : 'Baca seluruh bagian materi di atas untuk mengaktifkan pengumpulan studi kasus.'"></span>
            </div>

        </article>

        {{-- BAGIAN PENGERJAAN STUDI KASUS & UPLOAD FILE LAMPIRAN --}}
        <section id="section-studi-kasus" class="mt-8 rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 sm:p-8 lg:p-10 shadow-sm relative overflow-hidden">

            {{-- Info jika siswa belum selesai membaca --}}
            <div x-show="!bacaSelesai" x-transition.opacity
                class="rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/70 p-4 mb-6 flex items-center justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                        Selesaikan Bacaan Materi (<span x-text="progressPct() + '%'"></span>)
                    </h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                        Form studi kasus akan aktif otomatis setelah Anda selesai membaca seluruh bagian materi di atas.
                    </p>
                </div>
            </div>

            <div :class="!bacaSelesai ? 'opacity-50 pointer-events-none select-none' : 'opacity-100'" class="transition-opacity duration-300">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-5 border-b border-slate-200 dark:border-slate-800">
                    <div>
                        <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                            Evaluasi Praktik · {{ $kasusTipe ?: 'Studi Kasus' }}
                        </span>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white mt-0.5">
                            {{ $kasusJudul }}
                        </h2>
                    </div>

                    @if($pengumpulan)
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                            Sudah Dikumpulkan ({{ $pengumpulan->tanggal_kumpul?->translatedFormat('d M Y, H:i') }})
                        </span>
                    @endif
                </div>

                {{-- Instruksi Soal Studi Kasus --}}
                <div class="mt-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 p-5">
                    <p class="text-xs font-medium text-slate-400 mb-2">Instruksi Studi Kasus</p>
                    <div class="text-sm leading-relaxed text-slate-600 dark:text-slate-300 whitespace-pre-line">
                        {{ $kasusInstruksi ?: 'Tuliskan analisis penyelesaian studi kasus atau unggah file hasil pengerjaan Anda.' }}
                    </div>
                </div>

                {{-- Form Pengumpulan Jawaban & Upload File Lampiran --}}
                <form action="{{ route('siswa.materi.selesaikan', $materi->id) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-6">
                    @csrf

                    @if($kasusTipe === 'Pilihan ganda' && !empty($opsiA))
                        <div class="space-y-2.5">
                            <label class="block text-xs font-medium text-slate-400">
                                Pilih Jawaban Studi Kasus
                            </label>
                            @foreach(['A' => $opsiA, 'B' => $opsiB, 'C' => $opsiC, 'D' => $opsiD] as $key => $label)
                                @if(!empty($label))
                                    <label @click="jawabanInput = 'Opsi {{ $key }}: {{ addslashes($label) }}'"
                                        :class="jawabanInput.startsWith('Opsi {{ $key }}')
                                            ? 'border-teal-500 bg-teal-500/10'
                                            : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-teal-500/40'"
                                        class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition">
                                        <input type="radio" name="pilihan_opsi" value="Opsi {{ $key }}: {{ $label }}"
                                            x-model="jawabanInput" class="mt-1 text-teal-600 focus:ring-teal-500">
                                        <div class="text-sm text-slate-800 dark:text-slate-200">
                                            <span class="font-semibold mr-1">{{ $key }}.</span>
                                            <span>{{ $label }}</span>
                                        </div>
                                    </label>
                                @endif
                            @endforeach
                            <input type="hidden" name="jawaban_studi_kasus" :value="jawabanInput">
                        </div>
                    @elseif($kasusTipe === 'Kode program')
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-2">
                                Kode Program Penyelesaian
                            </label>
                            <div class="rounded-xl overflow-hidden border border-slate-700 bg-[#0d1117]">
                                <div class="px-4 py-2 bg-[#161b22] border-b border-slate-800 flex items-center justify-between text-xs font-medium text-slate-400 font-mono">
                                    <span>solution.code</span>
                                    <span>Source Code</span>
                                </div>
                                <textarea name="jawaban_studi_kasus" x-model="jawabanInput" rows="8"
                                    placeholder="/* Tuliskan kode program jawaban studi kasus Anda di sini... */"
                                    class="w-full bg-[#0d1117] p-4 font-mono text-sm text-teal-300 placeholder:text-slate-600 focus:outline-none resize-y"></textarea>
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-2">
                                Jawaban Studi Kasus
                            </label>
                            <textarea name="jawaban_studi_kasus" x-model="jawabanInput" rows="5"
                                placeholder="Tuliskan penjelasan atau jawaban studi kasus Anda..."
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 p-4 text-sm leading-relaxed text-slate-800 dark:text-slate-100 placeholder:text-slate-400 focus:outline-none focus:border-teal-500 resize-y"></textarea>
                        </div>
                    @endif

                    {{-- FITUR UPLOAD FILE LAMPIRAN --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-2">
                            Lampiran File Tugas / Proyek (Opsional)
                        </label>

                        <div @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop.prevent="handleFileDrop($event)"
                            :class="isDragging ? 'border-teal-500 bg-teal-500/10' : 'border-slate-300 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/50 hover:border-teal-500/50'"
                            class="relative rounded-xl border-2 border-dashed p-6 text-center transition-all">

                            <input type="file" name="file_lampiran" x-ref="fileInput"
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.zip,.html,.css,.js,.php"
                                @change="handleFileSelect($event)"
                                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">

                            <div class="w-10 h-10 mx-auto rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center mb-2.5">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>

                            <template x-if="!selectedFileName">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                        Tarik & letakkan file di sini, atau <span class="text-teal-600 dark:text-teal-400 underline">pilih dari perangkat</span>
                                    </p>
                                    <p class="text-xs font-medium text-slate-400 mt-1">
                                        Format: PDF, DOC/DOCX, JPG/PNG, ZIP, HTML, CSS, JS, PHP (Maks. 10 MB)
                                    </p>
                                </div>
                            </template>

                            <template x-if="selectedFileName">
                                <div class="inline-flex items-center gap-2 rounded-lg bg-teal-500/10 border border-teal-500/20 px-4 py-2 text-xs font-semibold text-teal-600 dark:text-teal-400">
                                    <span x-text="selectedFileName"></span>
                                    <span class="text-slate-400" x-text="'(' + selectedFileSize + ')'"></span>
                                </div>
                            </template>
                        </div>

                        @if($pengumpulan && $pengumpulan->file_lampiran)
                            <div class="mt-3 flex items-center justify-between rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-xs">
                                <span class="text-slate-600 dark:text-slate-300">
                                    File terlampir: <strong class="font-semibold">{{ $pengumpulan->nama_file_asli ?: basename($pengumpulan->file_lampiran) }}</strong> ({{ $pengumpulan->tipe_file }})
                                </span>
                                <a href="{{ $pengumpulan->file_url }}" target="_blank" download
                                    class="font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                                    Lihat / Unduh File ↗
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <p class="text-xs font-medium text-slate-400">
                            Pastikan jawaban atau lampiran Anda sudah sesuai dengan instruksi studi kasus.
                        </p>
                        <button type="submit" :disabled="!bacaSelesai"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 disabled:opacity-40 px-6 py-3 text-sm font-semibold text-white shadow-sm transition">
                            <span>{{ $pengumpulan ? 'Perbarui Pengumpulan' : 'Kirim Jawaban & Selesaikan' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- NAVIGASI ANTAR MATERI BAWAH --}}
        <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            @if($prevMateri)
                <a href="{{ route('siswa.materi.show', $prevMateri->id) }}"
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
                @if($materi->lock_state === 'selesai' || $nextMateri->lock_state !== 'terkunci')
                    <a href="{{ route('siswa.materi.show', $nextMateri->id) }}"
                        class="w-full sm:w-auto inline-flex items-center justify-end gap-3 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition">
                        <span>Materi Berikutnya: {{ $nextMateri->judul_materi ?: $nextMateri->judul }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                @else
                    <div class="w-full sm:w-auto inline-flex items-center gap-2 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-900/70 px-5 py-3 text-xs font-medium text-slate-400">
                        <span>Selesaikan studi kasus ini untuk membuka: {{ $nextMateri->judul_materi ?: $nextMateri->judul }}</span>
                    </div>
                @endif
            @endif
        </div>

        {{-- IMAGE LIGHTBOX / PREVIEW MODAL --}}
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

        {{-- MODAL KETUNTASAN MATERI --}}
        @if($celebration)
            <div x-cloak x-show="celebrationOpen"
                class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div x-show="celebrationOpen" x-transition.opacity @click="celebrationOpen = false"
                    class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"></div>

                <div x-show="celebrationOpen" x-transition
                    class="relative z-10 w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-7 text-center shadow-2xl">

                    <div class="w-14 h-14 mx-auto rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>

                    <h3 class="mt-4 text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                        {{ $celebration['title'] }}
                    </h3>

                    <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">
                        Materi & studi kasus <strong class="text-slate-800 dark:text-white">“{{ $celebration['materi_judul'] }}”</strong> telah selesai.
                    </p>

                    <div class="mt-5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 p-4">
                        <div class="text-xs font-medium text-slate-400">Status Level Siswa</div>
                        <div class="mt-1 text-3xl font-bold tracking-tight text-teal-600 dark:text-teal-400">
                            Level {{ $celebration['new_level'] }}
                        </div>
                        <div class="text-xs font-medium text-slate-400 mt-1">
                            {{ $celebration['rank_title'] }} · Progres {{ $celebration['progress_pct'] }}%
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col gap-2.5">
                        @if(!empty($celebration['unlocked_next_id']))
                            <a href="{{ route('siswa.materi.show', $celebration['unlocked_next_id']) }}"
                                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-700 py-3 text-sm font-semibold text-white shadow-sm transition">
                                <span>Lanjut ke: {{ $celebration['unlocked_next'] }}</span>
                            </a>
                        @endif
                        <a href="{{ route('siswa.materi', ['modul_id' => $materi->modul_id]) }}"
                            class="w-full inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 py-2.5 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Kembali ke Modul
                        </a>
                        <button type="button" @click="celebrationOpen = false"
                            class="w-full rounded-xl py-2 text-xs font-medium text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>

    <script>
        function materiReaderApp(config) {
            return {
                requiredKeys: config.requiredKeys || [],
                completedKeys: config.completedKeys || [],
                bacaSelesai: Boolean(config.bacaSelesai),
                celebrationOpen: Boolean(config.hasCelebration),
                jawabanInput: config.initialJawaban || '',

                lightboxOpen: false,
                lightboxUrl: '',
                lightboxCaption: '',

                isDragging: false,
                selectedFileName: '',
                selectedFileSize: '',
                syncTimeout: null,

                initTracker() {
                    if (this.requiredKeys.length === 0) {
                        this.bacaSelesai = true;
                        this.syncProgressToServer(true);
                        return;
                    }

                    const sections = document.querySelectorAll('[data-track-key]');
                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    const key = entry.target.getAttribute('data-track-key');
                                    setTimeout(() => {
                                        this.markKeyDone(key);
                                    }, 800);
                                }
                            });
                        }, { threshold: 0.35 });

                        sections.forEach(sec => observer.observe(sec));

                        const mediaEls = document.querySelectorAll('[data-track-media]');
                        const mediaObserver = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    const mKey = entry.target.getAttribute('data-track-media');
                                    setTimeout(() => {
                                        this.markKeyDone(mKey);
                                    }, 1200);
                                }
                            });
                        }, { threshold: 0.5 });

                        mediaEls.forEach(el => mediaObserver.observe(el));

                        const sentinel = document.getElementById('akhir-bacaan-sentinel');
                        if (sentinel) {
                            const endObserver = new IntersectionObserver((entries) => {
                                entries.forEach(entry => {
                                    if (entry.isIntersecting) {
                                        setTimeout(() => {
                                            this.requiredKeys.forEach(k => this.markKeyDone(k));
                                        }, 1000);
                                    }
                                });
                            }, { threshold: 0.6 });
                            endObserver.observe(sentinel);
                        }
                    } else {
                        this.bacaSelesai = true;
                    }
                },

                markKeyDone(key) {
                    if (!key) return;
                    if (!this.completedKeys.includes(key)) {
                        this.completedKeys.push(key);
                        this.checkAllCompleted();
                        this.debouncedSync();
                    }
                },

                markMediaSeen(mediaKey) {
                    this.markKeyDone(mediaKey);
                },

                checkAllCompleted() {
                    const allDone = this.requiredKeys.every(k => this.completedKeys.includes(k));
                    if (allDone && !this.bacaSelesai) {
                        this.bacaSelesai = true;
                        this.syncProgressToServer(true);
                    }
                },

                progressPct() {
                    if (this.bacaSelesai || this.requiredKeys.length === 0) return 100;
                    const done = this.requiredKeys.filter(k => this.completedKeys.includes(k)).length;
                    return Math.min(100, Math.round((done / this.requiredKeys.length) * 100));
                },

                debouncedSync() {
                    if (this.syncTimeout) clearTimeout(this.syncTimeout);
                    this.syncTimeout = setTimeout(() => {
                        this.syncProgressToServer(this.bacaSelesai);
                    }, 600);
                },

                syncProgressToServer(allCompleted = false) {
                    fetch(config.trackUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken
                        },
                        body: JSON.stringify({
                            completed_keys: this.completedKeys,
                            all_completed: allCompleted
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.baca_selesai) {
                            this.bacaSelesai = true;
                        }
                    })
                    .catch(() => {});
                },

                openLightbox(url, caption, mediaKey) {
                    this.lightboxUrl = url;
                    this.lightboxCaption = caption || '';
                    this.lightboxOpen = true;
                    if (mediaKey) {
                        this.markMediaSeen(mediaKey);
                    }
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    this.updateSelectedFileInfo(file);
                },

                handleFileDrop(event) {
                    this.isDragging = false;
                    const files = event.dataTransfer.files;
                    if (files && files.length > 0) {
                        this.$refs.fileInput.files = files;
                        this.updateSelectedFileInfo(files[0]);
                    }
                },

                updateSelectedFileInfo(file) {
                    if (!file) {
                        this.selectedFileName = '';
                        this.selectedFileSize = '';
                        return;
                    }
                    this.selectedFileName = file.name;
                    const kb = file.size / 1024;
                    this.selectedFileSize = kb >= 1024
                        ? (kb / 1024).toFixed(2) + ' MB'
                        : Math.round(kb) + ' KB';
                }
            };
        }
    </script>

</x-layouts.siswa>
