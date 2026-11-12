<!DOCTYPE html>
<html lang="id" x-data="dashboardApp()" x-init="init()" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }} - CodeQuest RPL</title>
    @vite('resources/css/app.css')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.12/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-slate-50 dark:bg-[#020817] text-slate-900 dark:text-slate-100 transition-colors duration-300 overflow-x-hidden">

    <!-- MOBILE OVERLAY -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false"
        class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>

    <div class="min-h-screen flex">

        <!-- SIDEBAR -->
        <x-siswa.sidebar />

        <!-- KONTEN -->
        <div class="flex-1 min-w-0">

            <!-- TOPBAR -->
            <x-siswa.topbar :title="$title ?? 'Dashboard Siswa'" />

            <!-- ISI HALAMAN -->
            <main class="p-4 lg:p-6">
                {{ $slot }}
            </main>

        </div>
    </div>

    <script>
        function dashboardApp() {
            return {
                darkMode: true,
                sidebarOpen: false,
                init() {
                    this.darkMode = localStorage.getItem('theme') !== 'light';
                    this.applyTheme();
                },
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                    this.applyTheme();
                },
                applyTheme() {
                    document.documentElement.classList.toggle('dark', this.darkMode);
                }
            }
        }
    </script>

</body>

</html>