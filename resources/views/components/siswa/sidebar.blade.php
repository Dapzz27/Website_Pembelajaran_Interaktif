<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="
        fixed lg:sticky lg:top-0
        z-50 lg:translate-x-0
        transition-all duration-300
        w-60 min-h-screen
        border-r border-slate-200 dark:border-slate-800
        bg-white dark:bg-[#071327]
        flex flex-col
    ">
    <!-- LOGO -->
    <div class="h-20 flex items-center px-6 border-b border-slate-200 dark:border-slate-800">
        <h1 class="font-bold text-xl tracking-tight">
            <span class="text-teal-600 dark:text-teal-400">CodeQuest</span><span class="text-slate-400 dark:text-slate-500">_RPL</span>
        </h1>
    </div>

    <!-- MENU -->
    <nav class="flex-1 px-4 py-5 space-y-1.5">
        @php
            $menus = [
                [
                    'route' => 'siswa.dashboard',
                    'label' => 'Dashboard',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />',
                ],
                [
                    'route' => 'siswa.materi',
                    'label' => 'Kelola Materi',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />',
                ],
                [
                    'route' => 'siswa.quiz',
                    'label' => 'Quiz',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
                ],
                [
                    'route' => 'siswa.statistik',
                    'label' => 'Statistik',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />',
                ],
            ];
        @endphp

        @foreach($menus as $menu)
            @if(request()->routeIs($menu['route']) || request()->routeIs($menu['route'] . '.*'))
                <a href="{{ route($menu['route']) }}" class="
                        flex items-center gap-3
                        px-4 py-3 rounded-xl
                        bg-teal-600 text-white
                        text-sm font-medium
                        shadow-sm transition-colors duration-200
                    ">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $menu['icon'] !!}
                    </svg>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @else
                <a href="{{ route($menu['route']) }}" class="
                        group relative flex items-center gap-3
                        px-4 py-3 rounded-xl
                        text-sm font-medium
                        text-slate-600 dark:text-slate-300
                        hover:bg-teal-500/10
                        hover:text-teal-600 dark:hover:text-teal-400
                        transition-colors duration-200
                    ">
                    <svg class="w-4 h-4 shrink-0 text-slate-400 group-hover:text-teal-600 dark:group-hover:text-teal-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                        {!! $menu['icon'] !!}
                    </svg>
                    <span>{{ $menu['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</aside>