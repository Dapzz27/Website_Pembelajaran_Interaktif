<x-layouts.siswa title="Evaluasi Quiz">

    @php
        $quizzesPayload = $quizzes->map(function ($q) {
            return [
                'id' => $q->id,
                'judul_quiz' => $q->judul_quiz,
                'deskripsi' => $q->deskripsi ?? '',
                'durasi_menit' => (int) $q->durasi_menit,
                'kkm' => (int) $q->kkm,
                'sudah_dikerjakan' => (bool) $q->sudah_dikerjakan,
                'nilai_tertinggi' => $q->nilai_tertinggi,
                'jumlah_percobaan' => (int) $q->jumlah_percobaan,
                'total_bobot' => (int) $q->total_bobot,
                'questions' => $q->arena_questions,
            ];
        })->values();

        $flashResult = session('quiz_result');
        $lulusCount = $quizzes->filter(fn ($q) => $q->sudah_dikerjakan && !is_null($q->nilai_tertinggi) && $q->nilai_tertinggi >= $q->kkm)->count();
    @endphp

    <div x-data="quizArenaApp({
        quizzes: {{ Js::from($quizzesPayload) }},
        initialResult: {{ Js::from($flashResult) }},
        csrfToken: '{{ csrf_token() }}',
        submitBaseUrl: '{{ url('/siswa/quiz') }}'
    })" class="max-w-6xl mx-auto space-y-6">

        {{-- HEADER EVALUASI QUIZ --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 lg:p-7 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="max-w-2xl">
                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                        Evaluasi Kompetensi · Level {{ $stats['level'] }}
                    </span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                        Daftar Paket Quiz
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                        Pilih paket evaluasi di bawah ini dan selesaikan seluruh soal pilihan ganda sebelum waktu berakhir.
                    </p>
                </div>

                <div class="flex items-center divide-x divide-slate-200 dark:divide-slate-800 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 px-5 py-3 flex-shrink-0">
                    <div class="pr-5">
                        <p class="text-xs font-medium text-slate-400">Quiz Selesai</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                            {{ $stats['quiz_selesai'] }} <span class="text-sm font-normal text-slate-400">/ {{ $stats['total_quiz'] }}</span>
                        </p>
                    </div>
                    <div class="pl-5">
                        <p class="text-xs font-medium text-slate-400">Rata-rata Skor</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-0.5">
                            {{ $stats['rata_skor_quiz'] }} <span class="text-sm font-normal text-slate-400">poin</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ASYMMETRICAL SPLIT VIEW (8/12 DAFTAR BARIS QUIZ + 4/12 PANEL PERFORMA SISWA) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            {{-- KOLOM KIRI (8/12): DAFTAR PAKET QUIZ VERTIKAL (SLEEK ROW LAYOUT) --}}
            <div class="lg:col-span-8 space-y-3.5">
                @forelse($quizzes as $idx => $quiz)
                    @php
                        $isPassed = $quiz->sudah_dikerjakan && !is_null($quiz->nilai_tertinggi) && $quiz->nilai_tertinggi >= $quiz->kkm;
                    @endphp
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-5 sm:p-6 shadow-sm hover:border-teal-500/40 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-5">

                            {{-- Kiri: Judul, Deskripsi Singkat, & Meta Inline --}}
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">
                                        Paket #{{ $idx + 1 }}
                                    </span>
                                    <span class="text-slate-300 dark:text-slate-700">•</span>
                                    @if($quiz->sudah_dikerjakan)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium {{ $isPassed ? 'bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' }}">
                                            {{ $isPassed ? 'Lulus KKM' : 'Sudah Dikerjakan' }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400">
                                            Belum Dikerjakan
                                        </span>
                                    @endif
                                </div>

                                <h2 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                                    {{ $quiz->judul_quiz }}
                                </h2>

                                <p class="text-sm text-slate-600 dark:text-slate-300">
                                    {{ $quiz->deskripsi ?: 'Kerjakan seluruh butir soal pilihan ganda dengan teliti sebelum waktu habis.' }}
                                </p>

                                {{-- Inline Meta Info --}}
                                <div class="pt-1 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-400">
                                    <span>{{ $quiz->questions->count() }} Soal</span>
                                    <span>·</span>
                                    <span>{{ $quiz->durasi_menit }} Menit</span>
                                    <span>·</span>
                                    <span>KKM {{ $quiz->kkm }}</span>
                                    @if(!is_null($quiz->nilai_tertinggi))
                                        <span>·</span>
                                        <span class="text-teal-600 dark:text-teal-400 font-semibold">
                                            Skor Tertinggi: {{ $quiz->nilai_tertinggi }}/100
                                        </span>
                                    @endif
                                    @if($quiz->jumlah_percobaan > 0)
                                        <span>·</span>
                                        <span>{{ $quiz->jumlah_percobaan }}x percobaan</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Kanan: Tombol Aksi Solid Biru Tosca --}}
                            <div class="flex sm:flex-col items-center sm:items-end justify-between gap-3 flex-shrink-0 pt-3 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800">
                                @if($quiz->questions->count() > 0)
                                    <button type="button"
                                        @click="startQuiz({{ $quiz->id }})"
                                        class="inline-flex items-center justify-center rounded-xl {{ $quiz->sudah_dikerjakan ? 'border border-teal-500/30 bg-teal-500/10 hover:bg-teal-500/20 text-teal-600 dark:text-teal-400' : 'bg-teal-600 hover:bg-teal-500 text-white shadow-sm' }} px-5 py-2.5 text-xs font-semibold transition">
                                        {{ $quiz->sudah_dikerjakan ? 'Kerjakan Ulang' : 'Mulai Quiz' }}
                                    </button>
                                @else
                                    <span class="text-xs font-medium text-slate-400">Menunggu soal dari guru</span>
                                @endif
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-10 text-center">
                        <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Belum Ada Paket Quiz</h3>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                            Guru belum menambahkan paket quiz. Silakan pelajari materi terlebih dahulu.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- KOLOM KANAN (4/12): RINGKASAN PERFORMA QUIZ SISWA --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm space-y-5">
                    <div>
                        <h2 class="text-base font-semibold tracking-tight text-slate-800 dark:text-white">
                            Ringkasan Performa Quiz
                        </h2>
                        <p class="text-sm text-slate-600 dark:text-slate-300 mt-0.5">
                            Rekapitulasi hasil pengerjaan seluruh paket evaluasi.
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-medium text-slate-400">Status Level Saat Ini</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">Level {{ $stats['level'] }} ({{ $stats['rank_title'] }})</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-medium text-slate-400">Paket Lulus KKM</span>
                            <span class="text-sm font-semibold text-teal-600 dark:text-teal-400">{{ $lulusCount }} / {{ $stats['total_quiz'] }} Paket</span>
                        </div>
                        <div class="flex items-center justify-between py-2.5 border-b border-slate-100 dark:border-slate-800">
                            <span class="text-xs font-medium text-slate-400">Skor Rata-rata</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ $stats['rata_skor_quiz'] }} / 100</span>
                        </div>
                    </div>

                    @if($quizzes->count() > 0)
                        <div class="space-y-2.5 pt-1">
                            <p class="text-xs font-medium text-slate-400">Daftar Nilai Tertinggi</p>
                            @foreach($quizzes as $qItem)
                                <div class="flex items-center justify-between gap-2 text-sm">
                                    <span class="text-slate-600 dark:text-slate-300 truncate">{{ $qItem->judul_quiz }}</span>
                                    @if(!is_null($qItem->nilai_tertinggi))
                                        <span class="font-semibold {{ $qItem->nilai_tertinggi >= $qItem->kkm ? 'text-teal-600 dark:text-teal-400' : 'text-amber-500' }} flex-shrink-0">
                                            {{ $qItem->nilai_tertinggi }}
                                        </span>
                                    @else
                                        <span class="text-xs font-medium text-slate-400 flex-shrink-0">Belum</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                        <a href="{{ route('siswa.statistik') }}"
                            class="block w-full text-center rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 hover:border-teal-500/40 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 transition">
                            Lihat Grafik Statistik Lengkap →
                        </a>
                    </div>
                </div>
            </div>

        </div>

        {{-- MODAL PENGERJAAN QUIZ INTERAKTIF --}}
        <div x-show="arenaOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-3 sm:p-6 overflow-y-auto"
            x-transition.opacity>

            <div class="w-full max-w-4xl rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] shadow-2xl overflow-hidden my-auto">

                <template x-if="activeQuiz">
                    <div>
                        {{-- Top Bar: Judul & Countdown Timer --}}
                        <div class="bg-slate-900 text-white px-6 py-4 flex flex-wrap items-center justify-between gap-4 border-b border-slate-800">
                            <div>
                                <h3 class="text-base font-semibold tracking-tight text-white" x-text="activeQuiz.judul_quiz"></h3>
                                <p class="text-xs font-medium text-slate-400 mt-0.5">
                                    Soal <span class="text-teal-400 font-semibold" x-text="currentIndex + 1"></span> dari <span x-text="activeQuiz.questions.length"></span>
                                    · Terjawab: <span class="text-teal-400 font-semibold" x-text="answeredCount()"></span>/<span x-text="activeQuiz.questions.length"></span>
                                </p>
                            </div>

                            {{-- Timer Hitung Mundur --}}
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 rounded-xl px-4 py-2 border font-mono text-sm font-bold"
                                    :class="remainingSeconds <= 60 ? 'border-red-500/50 bg-red-500/20 text-red-300 animate-pulse' : 'border-teal-500/30 bg-teal-500/10 text-teal-300'">
                                    <span x-text="formatTime(remainingSeconds)"></span>
                                </div>

                                <button type="button" @click="closeArena()"
                                    class="rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 text-xs font-medium text-slate-300 hover:bg-slate-700 transition">
                                    Tutup
                                </button>
                            </div>
                        </div>

                        {{-- Progress Bar Pengerjaan Soal Solid Biru Tosca --}}
                        <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-800">
                            <div class="h-full bg-teal-600 transition-all duration-300"
                                :style="'width: ' + progressPercentage() + '%'"></div>
                        </div>

                        <div class="p-6 sm:p-7">
                            {{-- Navigasi Nomor Soal --}}
                            <div class="flex flex-wrap items-center gap-2 pb-5 mb-5 border-b border-slate-200 dark:border-slate-800">
                                <template x-for="(q, idx) in activeQuiz.questions" :key="q.id">
                                    <button type="button"
                                        @click="goToQuestion(idx)"
                                        class="w-9 h-9 rounded-xl text-xs font-semibold transition-all flex items-center justify-center border"
                                        :class="{
                                            'bg-teal-600 text-white border-teal-600 shadow-sm': currentIndex === idx,
                                            'bg-teal-500/10 text-teal-600 dark:text-teal-400 border-teal-500/30': currentIndex !== idx && answers[q.id],
                                            'bg-slate-100 dark:bg-slate-900 text-slate-500 border-slate-200 dark:border-slate-800': currentIndex !== idx && !answers[q.id]
                                        }"
                                        x-text="idx + 1">
                                    </button>
                                </template>
                            </div>

                            {{-- Area Pertanyaan Aktif --}}
                            <template x-if="currentQuestion()">
                                <div class="space-y-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center rounded-lg bg-teal-500/10 px-2.5 py-1 text-xs font-medium text-teal-600 dark:text-teal-400">
                                                Pertanyaan #<span x-text="currentIndex + 1"></span>
                                            </span>
                                            <h4 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white leading-relaxed pt-1"
                                                x-text="currentQuestion().pertanyaan"></h4>
                                        </div>
                                        <span class="px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex-shrink-0">
                                            Bobot <span x-text="currentQuestion().bobot_nilai"></span> Poin
                                        </span>
                                    </div>

                                    {{-- Pilihan Opsi A, B, C, D --}}
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                        <template x-for="optKey in ['a', 'b', 'c', 'd']" :key="optKey">
                                            <button type="button"
                                                @click="selectOption(currentQuestion().id, optKey)"
                                                class="group text-left rounded-xl border p-4 transition-all duration-200 flex items-center gap-3.5"
                                                :class="answers[currentQuestion().id] === optKey
                                                    ? 'border-teal-500 bg-teal-500/10'
                                                    : 'border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/60 hover:border-teal-500/40'">
                                                <span class="w-8 h-8 rounded-lg font-bold text-xs uppercase flex items-center justify-center flex-shrink-0 transition-all"
                                                    :class="answers[currentQuestion().id] === optKey
                                                        ? 'bg-teal-600 text-white'
                                                        : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                                                    x-text="optKey">
                                                </span>
                                                <span class="text-sm font-medium text-slate-800 dark:text-slate-100 flex-1"
                                                    x-text="currentQuestion().opsi[optKey]"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            {{-- Navigasi Bawah --}}
                            <div class="flex flex-wrap items-center justify-between gap-3 mt-8 pt-5 border-t border-slate-200 dark:border-slate-800">
                                <button type="button"
                                    @click="prevQuestion()"
                                    :disabled="currentIndex === 0"
                                    class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300 disabled:opacity-40 hover:border-teal-500/40 transition">
                                    ← Soal Sebelumnya
                                </button>

                                <div class="flex items-center gap-3">
                                    <template x-if="currentIndex < activeQuiz.questions.length - 1">
                                        <button type="button"
                                            @click="nextQuestion()"
                                            class="rounded-xl border border-teal-500/30 bg-teal-500/10 hover:bg-teal-500/20 px-5 py-2.5 text-xs font-semibold text-teal-600 dark:text-teal-400 transition">
                                            Soal Berikutnya →
                                        </button>
                                    </template>

                                    <button type="button"
                                        @click="submitQuiz()"
                                        :disabled="isSubmitting"
                                        class="rounded-xl bg-teal-600 hover:bg-teal-500 px-6 py-2.5 text-xs font-semibold text-white shadow-sm transition disabled:opacity-50">
                                        <span x-show="!isSubmitting">Selesai & Kumpulkan Jawaban</span>
                                        <span x-show="isSubmitting">Menghitung Skor...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- MODAL HASIL AKHIR QUIZ --}}
        <div x-show="resultModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4"
            x-transition.opacity>

            <template x-if="quizResult">
                <div class="w-full max-w-lg rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 sm:p-8 text-center shadow-2xl">
                    <div class="w-14 h-14 rounded-xl mx-auto flex items-center justify-center mb-4 bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <template x-if="quizResult.level_naik">
                        <div class="inline-flex items-center rounded-lg bg-teal-500/10 border border-teal-500/20 px-3 py-1 text-xs font-semibold text-teal-600 dark:text-teal-400 mb-2">
                            Naik ke Level <span class="ml-1" x-text="quizResult.new_level"></span>
                        </div>
                    </template>

                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white"
                        x-text="quizResult.lulus ? 'Evaluasi Quiz Lulus KKM' : 'Evaluasi Quiz Selesai'"></h3>
                    <p class="text-xs font-medium text-slate-400 mt-1" x-text="quizResult.judul_quiz"></p>

                    <div class="my-6 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 p-5">
                        <p class="text-xs font-medium text-slate-400">Skor Akhir Anda</p>
                        <p class="text-3xl font-bold tracking-tight text-teal-600 dark:text-teal-400 my-1"
                            x-text="quizResult.skor"></p>
                        <p class="text-xs font-medium"
                            :class="quizResult.lulus ? 'text-teal-600 dark:text-teal-400' : 'text-amber-500'"
                            x-text="quizResult.lulus ? 'Melampaui KKM (' + quizResult.kkm + ')' : 'Belum mencapai KKM (' + quizResult.kkm + ')'"></p>

                        <div class="grid grid-cols-3 divide-x divide-slate-200 dark:divide-slate-800 mt-4 pt-4 border-t border-slate-200 dark:border-slate-800 text-xs">
                            <div>
                                <p class="text-slate-400">Benar</p>
                                <p class="text-sm font-semibold text-teal-600 dark:text-teal-400 mt-0.5" x-text="quizResult.benar"></p>
                            </div>
                            <div>
                                <p class="text-slate-400">Salah</p>
                                <p class="text-sm font-semibold text-red-500 mt-0.5" x-text="quizResult.salah"></p>
                            </div>
                            <div>
                                <p class="text-slate-400">Level</p>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white mt-0.5">Lv. <span x-text="quizResult.new_level"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <a href="{{ route('siswa.quiz') }}"
                            class="rounded-xl bg-teal-600 hover:bg-teal-500 px-6 py-2.5 text-xs font-semibold text-white transition">
                            Kembali ke Daftar Quiz
                        </a>
                        <a href="{{ route('siswa.statistik') }}"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-5 py-2.5 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:border-teal-500/40 transition">
                            Lihat Statistik Belajar
                        </a>
                    </div>
                </div>
            </template>
        </div>

    </div>

    <script>
        function quizArenaApp(config) {
            return {
                quizzes: config.quizzes || [],
                csrfToken: config.csrfToken,
                submitBaseUrl: config.submitBaseUrl,

                arenaOpen: false,
                activeQuiz: null,
                currentIndex: 0,
                answers: {},
                remainingSeconds: 0,
                elapsedSeconds: 0,
                timerInterval: null,
                isSubmitting: false,

                resultModalOpen: !!config.initialResult,
                quizResult: config.initialResult || null,

                startQuiz(quizId) {
                    const found = this.quizzes.find(q => Number(q.id) === Number(quizId));
                    if (!found || !found.questions || found.questions.length === 0) return;

                    this.activeQuiz = found;
                    this.currentIndex = 0;
                    this.answers = {};
                    this.remainingSeconds = (Number(found.durasi_menit) || 15) * 60;
                    this.elapsedSeconds = 0;
                    this.arenaOpen = true;

                    this.startTimer();
                },

                startTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timerInterval = setInterval(() => {
                        if (this.remainingSeconds > 0) {
                            this.remainingSeconds--;
                            this.elapsedSeconds++;
                        } else {
                            clearInterval(this.timerInterval);
                            this.submitQuiz(true);
                        }
                    }, 1000);
                },

                closeArena() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.arenaOpen = false;
                    this.activeQuiz = null;
                },

                currentQuestion() {
                    if (!this.activeQuiz || !this.activeQuiz.questions) return null;
                    return this.activeQuiz.questions[this.currentIndex] || null;
                },

                selectOption(questionId, optKey) {
                    this.answers[questionId] = optKey;
                },

                goToQuestion(idx) {
                    if (this.activeQuiz && idx >= 0 && idx < this.activeQuiz.questions.length) {
                        this.currentIndex = idx;
                    }
                },

                nextQuestion() {
                    if (this.activeQuiz && this.currentIndex < this.activeQuiz.questions.length - 1) {
                        this.currentIndex++;
                    }
                },

                prevQuestion() {
                    if (this.currentIndex > 0) {
                        this.currentIndex--;
                    }
                },

                answeredCount() {
                    return Object.keys(this.answers).length;
                },

                progressPercentage() {
                    if (!this.activeQuiz || !this.activeQuiz.questions.length) return 0;
                    return Math.round((this.answeredCount() / this.activeQuiz.questions.length) * 100);
                },

                formatTime(sec) {
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;
                    return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                },

                async submitQuiz(autoTimeout = false) {
                    if (!this.activeQuiz || this.isSubmitting) return;
                    this.isSubmitting = true;
                    if (this.timerInterval) clearInterval(this.timerInterval);

                    try {
                        const response = await fetch(`${this.submitBaseUrl}/${this.activeQuiz.id}/submit`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken
                            },
                            body: JSON.stringify({
                                answers: this.answers,
                                durasi_detik: this.elapsedSeconds
                            })
                        });

                        const data = await response.json();
                        if (data && data.success) {
                            this.arenaOpen = false;
                            this.quizResult = data.result;
                            this.resultModalOpen = true;
                        }
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            };
        }
    </script>

</x-layouts.siswa>