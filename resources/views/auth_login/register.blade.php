<!DOCTYPE html>
<html lang="id" x-data="pageTransition()">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Daftar Akun - CodeQuest RPL</title>
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
    <div class="fixed -top-28 -right-28 w-96 h-96 rounded-full bg-teal-500/5 border border-teal-500/10 pointer-events-none"></div>
    <div class="fixed -bottom-28 -left-28 w-96 h-96 rounded-full bg-teal-500/5 border border-teal-500/10 pointer-events-none"></div>

    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">

        <div class="w-full max-w-5xl rounded-3xl border border-slate-200/80 bg-white shadow-xl shadow-slate-200/60 overflow-hidden grid grid-cols-1 lg:grid-cols-12">

            {{-- SISI KIRI: PENGANTAR VISUAL & KEUNGGULAN PLATFORM --}}
            <div class="lg:col-span-5 bg-teal-500/5 border-b lg:border-b-0 lg:border-r border-slate-200/80 p-8 sm:p-10 flex flex-col justify-between relative overflow-hidden">

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

                    <div class="mt-8">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg bg-teal-500/10 border border-teal-500/20 text-xs font-semibold text-teal-700">
                            Registrasi Peserta Didik
                        </span>
                        <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 leading-snug">
                            Mulai Perjalanan Belajar Anda dari Level 0
                        </h1>
                        <p class="mt-2.5 text-sm text-slate-600 leading-relaxed">
                            Daftarkan akun Anda untuk mengakses seluruh modul pembelajaran, mengerjakan studi kasus, dan meningkatkan level kompetensi secara bertahap.
                        </p>
                    </div>

                    <div class="mt-7 space-y-3">
                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold text-teal-600">Langkah 1</p>
                            <p class="text-sm font-semibold text-slate-900 mt-0.5">Pilih Modul Pembelajaran</p>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Tersedia berbagai topik modul mulai dari Dasar Pemrograman, Analisis Jaringan, hingga Pemrograman Web.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
                            <p class="text-xs font-semibold text-teal-600">Langkah 2</p>
                            <p class="text-sm font-semibold text-slate-900 mt-0.5">Tuntaskan Sub-Bab & Studi Kasus</p>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Setiap sub-bab materi yang diselesaikan akan membuka materi berikutnya secara otomatis.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-200/80 flex items-center justify-between text-xs font-medium text-slate-500">
                    <span>Kurikulum Berbasis Kasus</span>
                    <span class="text-teal-600 font-semibold">CodeQuest_RPL</span>
                </div>
            </div>

            {{-- SISI KANAN: FORM REGISTRASI PUTIH BERSIH --}}
            <div class="lg:col-span-7 bg-white p-8 sm:p-10 lg:p-12 flex flex-col justify-center">

                <div class="mb-6">
                    <h2 class="text-2xl font-bold tracking-tight text-slate-900">
                        Buat Akun Baru
                    </h2>
                    <p class="text-slate-600 mt-1 text-sm">
                        Lengkapi informasi identitas di bawah ini untuk mendaftar.
                    </p>
                </div>

                @if(session('error'))
                    <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-700 text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif

                @if (isset($errors) && $errors->any())
                    <div class="mb-5 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-red-700 text-sm">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('register.proses') }}" method="POST" x-data="{
                    showPassword: false,
                    showConfirm: false
                }" class="space-y-4">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            Nama Lengkap
                        </label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required
                            placeholder="Masukkan nama lengkap Anda"
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none transition">
                    </div>

                    {{-- NIS / NIP --}}
                    <div>
                        <label for="nomor_induk" class="block text-sm font-semibold text-slate-800 mb-1.5">
                            NIS / NIP
                        </label>
                        <input id="nomor_induk" type="text" name="nomor_induk" value="{{ old('nomor_induk') }}" required
                            placeholder="Masukkan Nomor Induk Siswa atau NIP"
                            class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none transition">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Kata Sandi --}}
                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-800 mb-1.5">
                                Kata Sandi
                            </label>
                            <div class="relative">
                                <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                                    placeholder="Minimal 6 karakter"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 pr-16 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none transition">

                                <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 px-2 py-1 rounded-lg text-xs font-semibold text-teal-600 hover:text-teal-700 hover:bg-teal-500/10 transition">
                                    <span x-show="!showPassword">Lihat</span>
                                    <span x-cloak x-show="showPassword">Tutup</span>
                                </button>
                            </div>
                        </div>

                        {{-- Konfirmasi Kata Sandi --}}
                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-slate-800 mb-1.5">
                                Konfirmasi Kata Sandi
                            </label>
                            <div class="relative">
                                <input id="password_confirmation" :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required
                                    placeholder="Ulangi kata sandi"
                                    class="w-full rounded-xl bg-slate-50 border border-slate-200 px-4 py-2.5 pr-16 text-sm text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-teal-600 focus:ring-2 focus:ring-teal-500/20 outline-none transition">

                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 px-2 py-1 rounded-lg text-xs font-semibold text-teal-600 hover:text-teal-700 hover:bg-teal-500/10 transition">
                                    <span x-show="!showConfirm">Lihat</span>
                                    <span x-cloak x-show="showConfirm">Tutup</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Daftar (Solid Hijau/Biru Tosca #0D9488 -> hover #0F766E) --}}
                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3.5 rounded-xl text-sm font-semibold text-white bg-teal-600 hover:bg-teal-700 shadow-sm transition cursor-pointer">
                            Daftar Sekarang
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-5 border-t border-slate-100 text-center">
                    <p class="text-slate-600 text-sm">
                        Sudah memiliki akun?
                        <a href="{{ route('login') }}" @click.prevent="goLogin()"
                            class="text-teal-600 hover:text-teal-700 font-semibold ml-1 hover:underline">
                            Masuk di Sini
                        </a>
                    </p>
                </div>

            </div>

        </div>
    </main>

    {{-- Page Transition Cerah --}}
    <div x-cloak x-show="loading" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-[9999] bg-slate-50/90 backdrop-blur-sm flex items-center justify-center">
        <div class="w-11 h-11 rounded-full border-4 border-slate-200 border-t-teal-600 animate-spin"></div>
    </div>

    <script>
        function pageTransition() {
            return {
                loading: false,
                goLogin() {
                    this.loading = true;
                    setTimeout(() => {
                        window.location.href = "{{ route('login') }}";
                    }, 200);
                }
            }
        }
    </script>

</body>

</html>
