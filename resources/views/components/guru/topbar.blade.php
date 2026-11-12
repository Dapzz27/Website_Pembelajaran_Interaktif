@props(['title' => 'Dashboard Guru', 'subtitle' => 'Kelola pembelajaran'])

@php $user = auth()->user(); @endphp

<header class="
    sticky top-0 z-30 h-20 px-4 lg:px-8
    border-b border-slate-200 dark:border-slate-800
    bg-white/90 dark:bg-[#071327]/90
    backdrop-blur-xl
    flex items-center justify-between
">
    <!-- LEFT -->
    <div class="flex items-center gap-3">
        <!-- MOBILE TOGGLE -->
        <button
            @click="sidebarOpen = true"
            class="lg:hidden w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center"
        >
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
        <div>
            <h2 class="text-lg font-semibold tracking-tight text-slate-900 dark:text-white">
                {{ $title }}
            </h2>
        </div>
    </div>

    <!-- RIGHT -->
    <div class="flex items-center gap-3">

        <!-- DARK MODE -->
        <button
            @click="toggleTheme()"
            class="w-10 h-10 rounded-xl bg-teal-500/10 text-teal-600 dark:text-teal-400 border border-teal-500/20 flex items-center justify-center hover:bg-teal-500/15 transition"
        >
            <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
            </svg>
            <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                <circle cx="12" cy="12" r="5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" />
            </svg>
        </button>

        <!-- PROFILE DROPDOWN -->
        <div x-data="{ open: false }" class="relative">

            <button
                @click="open = !open"
                class="flex items-center gap-3 hover:bg-slate-100 dark:hover:bg-slate-800/80 px-2.5 py-1.5 rounded-xl transition"
            >
                <!-- AVATAR -->
                @if($user->foto)
                    <img
                        src="{{ asset('build/assets/' . $user->foto) }}"
                        alt="{{ $user->name }}"
                        class="w-9 h-9 rounded-full border-2 border-teal-500/40 object-cover"
                    >
                @else
                    <div class="w-9 h-9 rounded-full bg-teal-600 text-white flex items-center justify-center font-semibold text-xs">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif

                <!-- USER INFO -->
                <div class="hidden md:block text-left">
                    <div class="font-semibold text-sm text-slate-900 dark:text-white">
                        {{ $user->name }}
                    </div>
                    <div class="text-xs font-medium text-slate-400">
                        {{ $user->role == 1 ? 'Guru' : 'Siswa' }}
                    </div>
                </div>
            </button>

            <!-- DROPDOWN -->
            <div
                x-show="open"
                @click.outside="open = false"
                x-transition
                x-cloak
                class="absolute right-0 mt-3 w-52 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-[#08142c] shadow-xl overflow-hidden z-50"
            >
                <a href="{{ route('guru.profil') }}"
                    class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Lihat Profil
                </a>

                <a href="{{ route('guru.profil.edit') }}"
                    class="block px-4 py-2.5 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                    Edit Profil
                </a>
                <div class="border-t border-slate-100 dark:border-slate-800"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 w-full px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/10 transition">
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>
</header>