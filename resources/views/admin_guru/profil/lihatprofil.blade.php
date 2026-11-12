<x-layouts.guru title="Profil Guru">

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-teal-500/20 bg-teal-500/10 px-4 py-3 text-sm text-teal-600 dark:text-teal-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-4xl mx-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">

        <div class="flex flex-col md:flex-row gap-6 items-center md:items-start">

            {{-- FOTO --}}
            <div class="flex-shrink-0">
                @if($user->foto)
                    <img src="{{ asset('build/assets/' . $user->foto) }}" alt="{{ $user->name }}"
                        class="w-20 h-20 rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-teal-600 flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            {{-- DATA --}}
            <div class="flex-1 w-full">
                <div class="text-center md:text-left">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                        {{ $user->name }}
                    </h1>

                    <span class="inline-flex items-center mt-2 px-3 py-1 rounded-lg text-xs font-medium border border-teal-500/20 bg-teal-500/10 text-teal-600 dark:text-teal-400">
                        {{ $user->role == 1 ? 'Guru' : 'Siswa' }}
                    </span>
                </div>

                {{-- INFORMASI --}}
                <div class="grid sm:grid-cols-2 gap-4 mt-6">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-4">
                        <p class="text-xs font-medium text-slate-400">
                            Nomor Induk
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $user->nomor_induk }}
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/70 p-4">
                        <p class="text-xs font-medium text-slate-400">
                            Peran Akun
                        </p>
                        <p class="mt-1 text-sm font-semibold text-slate-900 dark:text-white">
                            {{ $user->role == 1 ? 'Guru' : 'Siswa' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- BUTTON --}}
        <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 flex flex-wrap gap-3">
            <a href="{{ route('guru.profil.edit') }}"
                class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-sm font-medium transition">
                Edit Profil
            </a>

            <button type="button" onclick="document.getElementById('modalHapusAkun').classList.remove('hidden')"
                class="px-5 py-2.5 rounded-xl border border-red-500/20 bg-red-500/10 text-red-600 dark:text-red-400 text-sm font-medium hover:bg-red-500/20 transition">
                Hapus Akun
            </button>
        </div>

    </div>

    {{-- MODAL HAPUS --}}
    <div id="modalHapusAkun" class="hidden fixed inset-0 z-50">
        <div class="w-full h-full bg-black/70 backdrop-blur-sm flex items-center justify-center px-4">
            <div class="w-full max-w-md rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-xl">

                <h3 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                    Hapus Akun
                </h3>

                <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                    Tindakan ini tidak dapat dibatalkan. Masukkan kata sandi untuk melanjutkan.
                </p>

                <form action="{{ route('guru.profil.destroy') }}" method="POST" class="mt-5">
                    @csrf
                    @method('DELETE')

                    <input type="password" name="password" required placeholder="Masukkan kata sandi"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-red-500">

                    @error('password')
                        <p class="mt-2 text-xs text-red-500">
                            {{ $message }}
                        </p>
                    @enderror

                    <div class="grid grid-cols-2 gap-3 mt-5">
                        <button type="button" onclick="document.getElementById('modalHapusAkun').classList.add('hidden')"
                            class="rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 py-2.5 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            Batal
                        </button>

                        <button type="submit"
                            class="rounded-xl border border-red-500/20 bg-red-600 text-white py-2.5 text-sm font-medium hover:bg-red-500 transition">
                            Hapus Akun
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</x-layouts.guru>