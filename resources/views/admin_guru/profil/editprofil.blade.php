<x-layouts.guru title="Edit Profil">

    @if(session('success'))
        <div class="mb-4 rounded-xl border border-teal-500/20 bg-teal-500/10 px-4 py-3 text-sm text-teal-600 dark:text-teal-400">
            {{ session('success') }}
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="mb-4 rounded-xl border border-red-500/20 bg-red-500/10 px-4 py-3">
            <ul class="text-sm text-red-500 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-4xl mx-auto rounded-2xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-[#08142c] p-6 shadow-sm">

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                Edit Profil
            </h1>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                Perbarui informasi akun dan kata sandi Anda.
            </p>
        </div>

        <form action="{{ route('guru.profil.update') }}" method="POST" enctype="multipart/form-data"
            x-data="{ preview: null }">
            @csrf
            @method('PUT')

            <div class="flex flex-col md:flex-row gap-5 items-center md:items-start mb-6">
                <div>
                    <template x-if="preview">
                        <img :src="preview" class="w-20 h-20 rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                    </template>

                    <template x-if="!preview">
                        @if($user->foto)
                            <img src="{{ asset('build/assets/' . $user->foto) }}"
                                class="w-20 h-20 rounded-2xl object-cover border border-slate-200 dark:border-slate-700">
                        @else
                            <div class="w-20 h-20 rounded-2xl bg-teal-600 flex items-center justify-center text-white text-xl font-bold">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                        @endif
                    </template>
                </div>

                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-400 mb-2">
                        Foto Profil
                    </label>
                    <input type="file" name="foto" accept="image/*"
                        @change="preview = URL.createObjectURL($event.target.files[0])"
                        class="w-full text-sm text-slate-500 file:mr-4 file:px-4 file:py-2 file:border-0 file:rounded-xl file:bg-teal-500/10 file:text-teal-600 dark:file:text-teal-400 hover:file:bg-teal-500/20">
                    <p class="text-xs font-medium text-slate-400 mt-2">
                        Format JPG atau PNG, maksimal 2MB.
                    </p>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">
                        Nama Lengkap
                    </label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-teal-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">
                        Nomor Induk
                    </label>
                    <input type="text" name="nomor_induk" value="{{ old('nomor_induk', $user->nomor_induk) }}" required
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-teal-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">
                        Kata Sandi Baru
                    </label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-teal-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-400 mb-1.5">
                        Konfirmasi Kata Sandi
                    </label>
                    <input type="password" name="password_confirmation" placeholder="Ulangi kata sandi baru"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 px-4 py-2.5 text-sm text-slate-900 dark:text-white focus:border-teal-500 outline-none">
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-5 border-t border-slate-200 dark:border-slate-800">
                <button type="submit"
                    class="px-5 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-500 text-white text-sm font-medium transition">
                    Simpan Perubahan
                </button>

                <a href="{{ route('guru.profil') }}"
                    class="px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-sm font-medium hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                    Batal
                </a>
            </div>
        </form>

    </div>

</x-layouts.guru>