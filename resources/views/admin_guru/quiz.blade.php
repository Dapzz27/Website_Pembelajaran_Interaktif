<x-layouts.guru title="Kelola Quiz">

    <div x-data="{
        openCreateQuiz: false,
        openEditQuiz: false,
        openDeleteQuiz: false,
        openQuestionModal: false,
        openDeleteQuestion: false,
        expandedQuizId: {{ session('open_quiz_id') ? (int) session('open_quiz_id') : ($quizzes->first()->id ?? 'null') }},

        quizForm: {
            id: null,
            judul_quiz: '',
            deskripsi: '',
            durasi_menit: 15,
            kkm: 70
        },
        deleteQuizTarget: { id: null, judul: '' },

        questionModalMode: 'create',
        questionForm: {
            id: null,
            quiz_id: null,
            quiz_judul: '',
            pertanyaan: '',
            opsi_a: '',
            opsi_b: '',
            opsi_c: '',
            opsi_d: '',
            jawaban_benar: 'a',
            bobot_nilai: 25
        },
        deleteQuestionTarget: { id: null, quiz_id: null, nomor: 1 },

        editQuiz(q) {
            this.quizForm = {
                id: q.id,
                judul_quiz: q.judul_quiz,
                deskripsi: q.deskripsi || '',
                durasi_menit: q.durasi_menit,
                kkm: q.kkm
            };
            this.openEditQuiz = true;
        },

        confirmDeleteQuiz(id, judul) {
            this.deleteQuizTarget = { id, judul };
            this.openDeleteQuiz = true;
        },

        openAddQuestion(quizId, quizJudul) {
            this.questionModalMode = 'create';
            this.questionForm = {
                id: null,
                quiz_id: quizId,
                quiz_judul: quizJudul,
                pertanyaan: '',
                opsi_a: '',
                opsi_b: '',
                opsi_c: '',
                opsi_d: '',
                jawaban_benar: 'a',
                bobot_nilai: 25
            };
            this.openQuestionModal = true;
        },

        openEditQuestion(quizId, quizJudul, q) {
            this.questionModalMode = 'edit';
            this.questionForm = {
                id: q.id,
                quiz_id: quizId,
                quiz_judul: quizJudul,
                pertanyaan: q.pertanyaan,
                opsi_a: q.opsi_a,
                opsi_b: q.opsi_b,
                opsi_c: q.opsi_c,
                opsi_d: q.opsi_d,
                jawaban_benar: q.jawaban_benar,
                bobot_nilai: q.bobot_nilai
            };
            this.openQuestionModal = true;
        },

        confirmDeleteQuestion(quizId, questionId, nomor) {
            this.deleteQuestionTarget = { quiz_id: quizId, id: questionId, nomor };
            this.openDeleteQuestion = true;
        }
    }" class="max-w-6xl mx-auto space-y-6">

        {{-- FLASH NOTIFICATION --}}
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4500)"
                x-transition
                class="flex items-center justify-between gap-3 rounded-xl border border-teal-500/25 bg-teal-500/10 px-5 py-3.5 text-sm font-medium text-teal-700 dark:text-teal-300">
                <span>{{ session('success') }}</span>
                <button type="button" @click="show = false" class="text-teal-600 dark:text-teal-300 hover:opacity-75">&times;</button>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-5 py-4 text-sm text-red-600 dark:text-red-300">
                <p class="font-semibold mb-1">Periksa kembali input Anda:</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- HEADER & STRIP METRIK TERPADU --}}
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] shadow-sm overflow-hidden">
            <div class="p-6 lg:p-7 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-medium text-teal-600 dark:text-teal-400">Manajemen Evaluasi</span>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                        Kelola Paket Quiz & Soal
                    </h1>
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                        Susun paket evaluasi, batas waktu pengerjaan, bobot nilai, dan kunci jawaban pilihan ganda.
                    </p>
                </div>

                <button type="button" @click="openCreateQuiz = true"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-3 text-sm font-semibold text-white shadow-sm transition flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Buat Paket Quiz</span>
                </button>
            </div>

            {{-- Strip Metrik Inline (divide-x) --}}
            <div class="border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-[#061024]/70 px-6 py-4 lg:px-7">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-0 lg:divide-x lg:divide-slate-200 dark:lg:divide-slate-800">
                    <div class="lg:pr-6">
                        <p class="text-xs font-medium text-slate-400">Total Paket Quiz</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $totalQuiz }} <span class="text-sm font-normal text-slate-400">paket</span>
                        </p>
                    </div>
                    <div class="lg:px-6">
                        <p class="text-xs font-medium text-slate-400">Total Butir Soal</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $totalSoal }} <span class="text-sm font-normal text-slate-400">soal</span>
                        </p>
                    </div>
                    <div class="lg:px-6">
                        <p class="text-xs font-medium text-slate-400">Total Pengerjaan</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $totalAttempts }} <span class="text-sm font-normal text-slate-400">kali</span>
                        </p>
                    </div>
                    <div class="lg:pl-6">
                        <p class="text-xs font-medium text-slate-400">Rata-rata Skor</p>
                        <p class="text-3xl font-bold tracking-tight text-slate-900 dark:text-white mt-1">
                            {{ $rataSkor }} <span class="text-sm font-normal text-slate-400">poin</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- DAFTAR PAKET QUIZ --}}
        <div class="space-y-4">
            @forelse($quizzes as $idx => $quiz)
                @php
                    $quizJson = [
                        'id' => $quiz->id,
                        'judul_quiz' => $quiz->judul_quiz,
                        'deskripsi' => $quiz->deskripsi ?? '',
                        'durasi_menit' => (int) $quiz->durasi_menit,
                        'kkm' => (int) $quiz->kkm,
                    ];
                @endphp
                <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] overflow-hidden shadow-sm">
                    {{-- Header Paket Quiz --}}
                    <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 font-bold text-sm flex items-center justify-center flex-shrink-0">
                                {{ $idx + 1 }}
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                                        {{ $quiz->judul_quiz }}
                                    </h2>
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $quiz->durasi_menit }} Menit
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20">
                                        KKM {{ $quiz->kkm }}
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                        {{ $quiz->questions->count() }} Soal ({{ $quiz->questions->sum('bobot_nilai') }} Poin)
                                    </span>
                                </div>
                                @if($quiz->deskripsi)
                                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                                        {{ $quiz->deskripsi }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Tombol Aksi Paket Quiz --}}
                        <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                            <button type="button"
                                @click="openAddQuestion({{ $quiz->id }}, {{ Js::from($quiz->judul_quiz) }})"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-teal-500/10 border border-teal-500/20 px-3.5 py-2 text-xs font-semibold text-teal-600 dark:text-teal-400 hover:bg-teal-500/20 transition">
                                + Tambah Soal
                            </button>

                            <button type="button"
                                @click="expandedQuizId = (expandedQuizId === {{ $quiz->id }} ? null : {{ $quiz->id }})"
                                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3.5 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:border-teal-500/40 transition">
                                <span x-text="expandedQuizId === {{ $quiz->id }} ? 'Sembunyikan Soal' : 'Daftar Soal ({{ $quiz->questions->count() }})'"></span>
                            </button>

                            <button type="button"
                                @click="editQuiz({{ Js::from($quizJson) }})"
                                class="inline-flex items-center rounded-xl border border-slate-200 dark:border-slate-700 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:border-teal-500/40 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                Edit
                            </button>

                            <button type="button"
                                @click="confirmDeleteQuiz({{ $quiz->id }}, {{ Js::from($quiz->judul_quiz) }})"
                                class="inline-flex items-center rounded-xl border border-red-500/20 px-3 py-2 text-xs font-semibold text-red-500 hover:bg-red-500/10 transition">
                                Hapus
                            </button>
                        </div>
                    </div>

                    {{-- Panel Daftar Soal --}}
                    <div x-show="expandedQuizId === {{ $quiz->id }}"
                        class="border-t border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-[#061024]/70 p-5 sm:p-6">

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                Daftar Pertanyaan Pilihan Ganda ({{ $quiz->questions->count() }} Soal)
                            </h3>
                            <button type="button"
                                @click="openAddQuestion({{ $quiz->id }}, {{ Js::from($quiz->judul_quiz) }})"
                                class="text-xs font-semibold text-teal-600 dark:text-teal-400 hover:underline">
                                + Tambah Butir Soal
                            </button>
                        </div>

                        @if($quiz->questions->isEmpty())
                            <div class="rounded-xl border border-dashed border-slate-200 dark:border-slate-800 p-6 text-center">
                                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Belum ada soal pada paket quiz ini.</p>
                                <p class="text-xs font-medium text-slate-400 mt-1">Klik tombol "+ Tambah Soal" untuk membuat pertanyaan pilihan ganda.</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($quiz->questions as $qIdx => $q)
                                    @php
                                        $qData = [
                                            'id' => $q->id,
                                            'pertanyaan' => $q->pertanyaan,
                                            'opsi_a' => $q->opsi_a,
                                            'opsi_b' => $q->opsi_b,
                                            'opsi_c' => $q->opsi_c,
                                            'opsi_d' => $q->opsi_d,
                                            'jawaban_benar' => strtolower($q->jawaban_benar),
                                            'bobot_nilai' => (int) $q->bobot_nilai,
                                        ];
                                    @endphp
                                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-4 shadow-sm">
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                            <div class="flex items-start gap-3">
                                                <span class="w-7 h-7 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 font-semibold text-xs flex items-center justify-center flex-shrink-0 mt-0.5">
                                                    {{ $qIdx + 1 }}
                                                </span>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">
                                                        {{ $q->pertanyaan }}
                                                    </p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-3">
                                                        @foreach(['a' => $q->opsi_a, 'b' => $q->opsi_b, 'c' => $q->opsi_c, 'd' => $q->opsi_d] as $optKey => $optVal)
                                                            <div class="flex items-center gap-2 rounded-lg px-3 py-1.5 text-xs border {{ strtolower($q->jawaban_benar) === $optKey ? 'border-teal-500/40 bg-teal-500/10 text-teal-700 dark:text-teal-300 font-semibold' : 'border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                                                                <span class="uppercase font-bold">{{ $optKey }}.</span>
                                                                <span class="flex-1">{{ $optVal }}</span>
                                                                @if(strtolower($q->jawaban_benar) === $optKey)
                                                                    <span>Kunci</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2 self-end sm:self-start flex-shrink-0">
                                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                                    Bobot: {{ $q->bobot_nilai }}
                                                </span>
                                                <button type="button"
                                                    @click="openEditQuestion({{ $quiz->id }}, {{ Js::from($quiz->judul_quiz) }}, {{ Js::from($qData) }})"
                                                    class="px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-700 dark:text-slate-300 hover:border-teal-500/40 hover:text-teal-600 dark:hover:text-teal-400 transition">
                                                    Edit
                                                </button>
                                                <button type="button"
                                                    @click="confirmDeleteQuestion({{ $quiz->id }}, {{ $q->id }}, {{ $qIdx + 1 }})"
                                                    class="px-2.5 py-1 rounded-lg border border-red-500/20 text-xs font-medium text-red-500 hover:bg-red-500/10 transition">
                                                    Hapus
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-10 text-center">
                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Belum Ada Paket Quiz</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 max-w-md mx-auto mt-1">
                        Tambahkan paket quiz pertama Anda untuk memberikan latihan soal kepada siswa.
                    </p>
                    <button type="button" @click="openCreateQuiz = true"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm">
                        + Buat Paket Quiz Pertama
                    </button>
                </div>
            @endforelse
        </div>

        {{-- MODAL TAMBAH PAKET QUIZ --}}
        <div x-show="openCreateQuiz" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
            x-transition.opacity>
            <div @click.away="openCreateQuiz = false"
                class="w-full max-w-lg rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Buat Paket Quiz Baru</h3>
                    <button type="button" @click="openCreateQuiz = false" class="text-slate-400 hover:text-slate-200">&times;</button>
                </div>

                <form action="{{ route('guru.quiz.store') }}" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Judul Paket Quiz *
                        </label>
                        <input type="text" name="judul_quiz" required placeholder="Contoh: Quiz 1: Dasar HTML & Algoritma"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Deskripsi Singkat
                        </label>
                        <textarea name="deskripsi" rows="2" placeholder="Jelaskan cakupan materi pada quiz ini..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Durasi Waktu (Menit) *
                            </label>
                            <input type="number" name="durasi_menit" value="15" min="1" max="180" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Nilai KKM (0-100) *
                            </label>
                            <input type="number" name="kkm" value="70" min="0" max="100" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openCreateQuiz = false"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm">
                            Simpan Paket Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL EDIT PAKET QUIZ --}}
        <div x-show="openEditQuiz" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
            x-transition.opacity>
            <div @click.away="openEditQuiz = false"
                class="w-full max-w-lg rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Edit Paket Quiz</h3>
                    <button type="button" @click="openEditQuiz = false" class="text-slate-400 hover:text-slate-200">&times;</button>
                </div>

                <form :action="'{{ url('/admin_guru/quiz') }}/' + quizForm.id" method="POST" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Judul Paket Quiz *
                        </label>
                        <input type="text" name="judul_quiz" x-model="quizForm.judul_quiz" required
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Deskripsi Singkat
                        </label>
                        <textarea name="deskripsi" rows="2" x-model="quizForm.deskripsi"
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Durasi Waktu (Menit) *
                            </label>
                            <input type="number" name="durasi_menit" x-model="quizForm.durasi_menit" min="1" max="180" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Nilai KKM (0-100) *
                            </label>
                            <input type="number" name="kkm" x-model="quizForm.kkm" min="0" max="100" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openEditQuiz = false"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL HAPUS PAKET QUIZ --}}
        <div x-show="openDeleteQuiz" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
            x-transition.opacity>
            <div @click.away="openDeleteQuiz = false"
                class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl text-center">
                <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">Hapus Paket Quiz?</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1.5">
                    Anda yakin ingin menghapus <strong class="text-slate-800 dark:text-slate-200" x-text="deleteQuizTarget.judul"></strong> beserta seluruh soal dan riwayat nilainya?
                </p>

                <form :action="'{{ url('/admin_guru/quiz') }}/' + deleteQuizTarget.id" method="POST" class="mt-5 flex justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="openDeleteQuiz = false"
                        class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-red-600 px-5 py-2.5 text-xs font-semibold text-white hover:bg-red-500 transition">
                        Ya, Hapus
                    </button>
                </form>
            </div>
        </div>

        {{-- MODAL TAMBAH / EDIT SOAL PILIHAN GANDA --}}
        <div x-show="openQuestionModal" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 overflow-y-auto"
            x-transition.opacity>
            <div @click.away="openQuestionModal = false"
                class="w-full max-w-2xl rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl my-8">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white"
                            x-text="questionModalMode === 'create' ? 'Tambah Butir Soal Pilihan Ganda' : 'Edit Butir Soal'"></h3>
                        <p class="text-xs font-medium text-teal-600 dark:text-teal-400 mt-0.5" x-text="questionForm.quiz_judul"></p>
                    </div>
                    <button type="button" @click="openQuestionModal = false" class="text-slate-400 hover:text-slate-200">&times;</button>
                </div>

                <form :action="questionModalMode === 'create'
                        ? ('{{ url('/admin_guru/quiz') }}/' + questionForm.quiz_id + '/soal')
                        : ('{{ url('/admin_guru/quiz') }}/' + questionForm.quiz_id + '/soal/' + questionForm.id)"
                    method="POST" class="mt-5 space-y-4">
                    @csrf
                    <template x-if="questionModalMode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">
                            Pertanyaan Soal *
                        </label>
                        <textarea name="pertanyaan" rows="3" x-model="questionForm.pertanyaan" required
                            placeholder="Tuliskan pertanyaan pilihan ganda dengan jelas..."
                            class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Opsi A *</label>
                            <input type="text" name="opsi_a" x-model="questionForm.opsi_a" required placeholder="Jawaban A"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Opsi B *</label>
                            <input type="text" name="opsi_b" x-model="questionForm.opsi_b" required placeholder="Jawaban B"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Opsi C *</label>
                            <input type="text" name="opsi_c" x-model="questionForm.opsi_c" required placeholder="Jawaban C"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1">Opsi D *</label>
                            <input type="text" name="opsi_d" x-model="questionForm.opsi_d" required placeholder="Jawaban D"
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-3.5 py-2 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Kunci Jawaban Benar *
                            </label>
                            <select name="jawaban_benar" x-model="questionForm.jawaban_benar" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm font-semibold text-teal-600 dark:text-teal-400 focus:outline-none focus:border-teal-500">
                                <option value="a">Opsi A</option>
                                <option value="b">Opsi B</option>
                                <option value="c">Opsi C</option>
                                <option value="d">Opsi D</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">
                                Bobot Nilai Poin *
                            </label>
                            <input type="number" name="bobot_nilai" x-model="questionForm.bobot_nilai" min="1" max="100" required
                                class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:outline-none focus:border-teal-500">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="openQuestionModal = false"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2.5 text-xs font-semibold text-slate-600 dark:text-slate-300">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-teal-600 hover:bg-teal-500 px-5 py-2.5 text-xs font-semibold text-white shadow-sm">
                            Simpan Butir Soal
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL HAPUS SOAL --}}
        <div x-show="openDeleteQuestion" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4"
            x-transition.opacity>
            <div @click.away="openDeleteQuestion = false"
                class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-2xl text-center">
                <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                    Hapus Soal Nomor #<span x-text="deleteQuestionTarget.nomor"></span>?
                </h3>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                    Soal yang dihapus tidak dapat dikembalikan.
                </p>

                <form :action="'{{ url('/admin_guru/quiz') }}/' + deleteQuestionTarget.quiz_id + '/soal/' + deleteQuestionTarget.id"
                    method="POST" class="mt-5 flex justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="openDeleteQuestion = false"
                        class="rounded-xl border border-slate-200 dark:border-slate-700 px-4 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="rounded-xl bg-red-600 px-5 py-2 text-xs font-semibold text-white hover:bg-red-500 transition">
                        Ya, Hapus Soal
                    </button>
                </form>
            </div>
        </div>

    </div>

</x-layouts.guru>