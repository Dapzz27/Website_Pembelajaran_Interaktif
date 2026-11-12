<!DOCTYPE html>
<html lang="id" x-data="pageTransition()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - CodeQuest RPL</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.12/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
        .bg-grid-teal {
            background-image:
                linear-gradient(to right, rgba(13, 148, 136, 0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(13, 148, 136, 0.06) 1px, transparent 1px);
            background-size: 28px 28px;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased overflow-x-hidden overflow-y-auto relative">

    {{-- Latar Belakang Pola Grid Tipis & Aksen Geometris Tosca Lembut --}}
    <div class="fixed inset-0 bg-grid-teal pointer-events-none"></div>
    <div class="fixed -top-28 -left-28 w-96 h-96 rounded-full bg-teal-500/5 border border-teal-500/10 pointer-events-none"></div>
    <div class="fixed -bottom-28 -right-28 w-96 h-96 rounded-full bg-teal-500/5 border border-teal-500/10 pointer-events-none"></div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">

        <div class="w-full max-w-5xl rounded-3xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/60 overflow-hidden grid grid-cols-1 lg:grid-cols-12">

            {{-- SISI KIRI: PENGANTAR VISUAL & FITUR PLATFORM CODEQUEST_RPL --}}
            <div class="lg:col-span-6 bg-teal-500/5 border-b lg:border-b-0 lg:border-r border-slate-200/80 p-8 sm:p-10 lg:p-12 flex flex-col justify-between relative overflow-hidden">

                <div>
                    {{-- Identitas Brand --}}
                    <div class="inline-flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-sm">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-xl font-bold tracking-tight text-slate-900">
                                Code<span class="text-teal-600">Quest</span><span class="text-slate-400 font-semibold">_RPL</span>
                            </span>
                            <p class="text-xs font-medium text-slate-500">Learning Management System</p>
                        </div>
                    </div>

                    {{-- Headline Utama --}}
                    <div class="mt-8">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 border border-teal-500/20 text-xs font-semibold text-teal-700">
                            Pembelajaran Rekayasa Perangkat Lunak
                        </span>
                        <h1 class="mt-3 text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 leading-snug">
                            Kuasai Kompetensi Melalui Modul Terstruktur & Studi Kasus
                        </h1>
                        <p class="mt-2.5 text-sm text-slate-600 leading-relaxed">
                            Pelajari setiap modul secara bertahap, selesaikan tantangan studi kasus nyata, dan ukur pencapaian kompetensi Anda secara langsung.
                        </p>
                    </div>

                    {{-- Kartu Fitur Ringkas --}}
                    <div class="mt-7 space-y-3">
                        <div class="flex items-start gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 border border-teal-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Modul Pembelajaran Bertahap</h3>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                    Materi disusun per modul dan sub-bab dengan sistem pembukaan berurutan.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <div class="w-9 h-9 rounded-xl bg-teal-500/10 text-teal-600 border border-teal-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900">Studi Kasus & Evaluasi Quiz</h3>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                    Uji pemahaman melalui latihan pemecahan kasus, kode program, dan paket evaluasi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Kiri --}}
                <div class="mt-8 pt-5 border-t border-slate-200/80 flex items-center justify-between text-xs font-medium text-slate-500">
                    <span>Ruang Belajar Guru & Siswa</span>
                    <span class="text-teal-600 font-semibold">CodeQuest_RPL</span>
                </div>
            </div>

            {{-- SISI KANAN: FORM LOGIN PUTIH BERSIH --}}
            <div class="lg:col-span-6 bg-white p-8 sm:p-10 lg:p-12 flex flex-col justify-center">

                <div class="mb-7">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                        Selamat Datang Kembali
                    </h2>
                    <p class="text-slate-600 mt-1.5 text-sm">
                        Masuk ke akun Anda menggunakan NIS / NIP dan kata sandi.
                    </p>
                </div>

                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-5 flex items-center justify-between rounded-xl border border-teal-500/30 bg-teal-500/10 px-4 py-3 text-teal-800 text-sm font-medium">
                        <span>{{ session('success') }}</span>
                        <button type="button" @click="show = false" class="text-teal-700 hover:text-teal-900 ml-2 font-bold">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition
                        class="mb-5 flex items-center justify-between rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-700 text-sm font-medium">
                        <span>{{ session('error') }}</span>
                        <button type="button" @click="show = false" class="text-red-600 hover:text-red-800 ml-2 font-bold">&times;</button>
                    </div>
                @endif

                @if(isset($errors) && $errors->any())
                    <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-700 text-sm">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('login.proses') }}" method="POST" x-data="{ showPassword: false }" class="space-y-5">
                    @csrf

                    {{-- NIS / NIP --}}
                    <div>
                        <label for="nomor_induk" class="block text-sm font-semibold text-slate-800 mb-2">
                            NIS / NIP
                        </label>
                        <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required
                            placeholder="Masukkan NIS (Siswa) atau NIP (Guru)"
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none transition">
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-800 mb-2">
                            Kata Sandi
                        </label>
                        <div class="relative">
                            <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                placeholder="Masukkan kata sandi Anda"
                                class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 pr-20 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none transition">

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 px-2 py-1 rounded-lg text-xs font-semibold text-teal-600 hover:text-teal-700 hover:bg-teal-500/10 transition">
                                <span x-show="!showPassword">Lihat</span>
                                <span x-cloak x-show="showPassword">Tutup</span>
                            </button>
                        </div>
                    </div>

                    {{-- Tombol Masuk (Solid Hijau/Biru Tosca #0D9488 -> hover #0F766E) --}}
                    <div class="pt-1">
                        <button type="submit"
                            class="w-full py-3.5 rounded-xl text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition cursor-pointer">
                            Masuk ke Akun
                        </button>
                    </div>
                </form>

                {{-- Tautan Daftar --}}
                <div class="mt-7 pt-6 border-t border-slate-100 text-center">
                    <p class="text-slate-600 text-sm">
                        Belum memiliki akun belajar?
                        <a href="{{ route('register') }}" @click.prevent="goRegister()"
                            class="text-teal-600 hover:text-teal-700 font-semibold ml-1 hover:underline">
                            Daftar Akun Baru
                        </a>
                    </p>
                </div>

            </div>

        </div>

    </div>

    {{-- Page Transition Cerah --}}
    <div x-cloak x-show="loading" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-[9999] bg-slate-50/90 backdrop-blur-sm flex items-center justify-center">
        <div class="w-11 h-11 rounded-full border-4 border-slate-200 border-t-teal-600 animate-spin"></div>
    </div>

    <script>
        function pageTransition() {
            return {
                loading: false,
                goRegister() {
                    this.loading = true;
                    setTimeout(() => {
                        window.location.href = "{{ route('register') }}";
                    }, 200);
                }
            }
        }
    </script>

</body>

</html>
