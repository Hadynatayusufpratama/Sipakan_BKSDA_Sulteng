<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#064e3b">
    <title>SIPAKAN - BKSDA Sulawesi Tengah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bksda: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            600: '#059669',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased selection:bg-emerald-600 selection:text-white">
    @php($isPetugas = Request::routeIs('petugas.*'))

    @if($isPetugas)
        <div class="mx-auto flex min-h-screen max-w-md flex-col bg-slate-50 shadow-2xl">
            <header class="sticky top-0 z-40 border-b border-emerald-700/40 bg-gradient-to-r from-bksda-950 to-bksda-900 px-4 py-3 text-white shadow-lg">
                <div class="flex items-center justify-between gap-2.5">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-9 w-9 shrink-0 rounded-full bg-white object-contain p-0.5 shadow-inner">
                        <div class="min-w-0">
                            <h1 class="truncate text-xs font-bold tracking-tight">SIPAKAN BKSDA</h1>
                            <p class="truncate text-[9px] font-medium text-emerald-300">Portal Petugas Lapangan</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <div class="hidden items-center gap-1.5 rounded-lg border border-emerald-600/30 bg-bksda-900/80 px-2 py-1 text-[9px] font-medium text-emerald-100 min-[390px]:inline-flex">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                            Palu, Sulteng
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-lg border border-emerald-500/30 px-2.5 py-1.5 text-[10px] font-semibold text-emerald-50 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
                <div class="mt-2 flex items-center gap-1.5 text-[9px] font-medium text-emerald-100/70 min-[390px]:hidden">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                    Palu, Sulteng
                </div>
            </header>

            <main class="w-full flex-1 space-y-4 px-4 py-5 pb-28">
                @yield('content')
            </main>

            <nav aria-label="Navigasi petugas" class="fixed bottom-0 left-1/2 z-50 flex w-full max-w-md -translate-x-1/2 items-center justify-around rounded-t-3xl border-t border-slate-200/80 bg-white/95 px-2 py-2.5 shadow-2xl backdrop-blur-xl">
                <a href="{{ route('petugas.dashboard') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('petugas.dashboard') && request()->query('tab') !== 'tugas' ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                    <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('petugas.dashboard') && request()->query('tab') !== 'tugas' ? 'bg-emerald-100' : '' }}" aria-hidden="true">🏠</span>
                    <span class="text-[9px]">Home</span>
                </a>
                <a href="{{ route('petugas.dashboard', ['tab' => 'tugas']).'#tugas' }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('petugas.dashboard') && request()->query('tab') === 'tugas' ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                    <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('petugas.dashboard') && request()->query('tab') === 'tugas' ? 'bg-emerald-100' : '' }}" aria-hidden="true">📋</span>
                    <span class="text-[9px]">Tugas</span>
                </a>
                <a href="{{ route('petugas.riwayat') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('petugas.riwayat') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                    <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('petugas.riwayat') ? 'bg-emerald-100' : '' }}" aria-hidden="true">🕒</span>
                    <span class="text-[9px]">Riwayat</span>
                </a>
                <a href="{{ route('petugas.profil') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('petugas.profil') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                    <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('petugas.profil') ? 'bg-emerald-100' : '' }}" aria-hidden="true">👤</span>
                    <span class="text-[9px]">Profil</span>
                </a>
            </nav>
        </div>
    @else
        <div class="relative mx-auto flex min-h-screen w-full max-w-md flex-col overflow-y-auto bg-slate-50 shadow-2xl">
            <header class="sticky top-0 z-40 border-b border-emerald-700/40 bg-gradient-to-r from-bksda-950 to-bksda-900 px-4 py-3 text-white shadow-lg">
                <div class="flex items-center justify-between gap-2.5">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-9 w-9 shrink-0 rounded-full bg-white object-contain p-0.5 shadow-inner">
                        <div class="min-w-0">
                            <p class="truncate text-[9px] font-bold uppercase tracking-[0.14em] text-emerald-300">Panel Administrasi</p>
                            <h1 class="truncate text-xs font-bold tracking-tight">SIPAKAN BKSDA Sulteng</h1>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="shrink-0">
                        @csrf
                        <button type="submit" class="rounded-lg border border-emerald-500/30 px-2.5 py-1.5 text-[10px] font-semibold text-emerald-50 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-300">
                            Keluar
                        </button>
                    </form>
                </div>
                <div class="mt-2 flex items-center gap-1.5 pl-11 text-[9px] font-medium text-emerald-100/80">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                    <span class="rounded-full border border-white/10 bg-white/10 px-2 py-1">Palu, Sulteng</span>
                </div>
            </header>

            <main class="w-full flex-1 space-y-4 px-4 py-5 pb-28">
                @yield('content')
            </main>
        </div>

        <nav aria-label="Navigasi admin" class="fixed bottom-0 left-1/2 z-50 flex w-full max-w-md -translate-x-1/2 items-center justify-around rounded-t-3xl border-t border-slate-200/80 bg-white/95 px-2 py-2.5 shadow-2xl backdrop-blur-xl">
            <a href="{{ route('dashboard') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('dashboard') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('dashboard') ? 'bg-emerald-100' : '' }}" aria-hidden="true">🏠</span>
                <span class="text-[9px]">Home</span>
            </a>
            <a href="{{ route('satwa.index') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::is('satwa*') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                <span class="rounded-xl p-1.5 text-lg {{ Request::is('satwa*') ? 'bg-emerald-100' : '' }}" aria-hidden="true">🐾</span>
                <span class="text-[9px]">Satwa</span>
            </a>
            <a href="{{ route('jadwal.index') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('jadwal.index') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('jadwal.index') ? 'bg-emerald-100' : '' }}" aria-hidden="true">📋</span>
                <span class="text-[9px]">Jadwal</span>
            </a>
            <a href="{{ route('riwayat') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('riwayat') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('riwayat') ? 'bg-emerald-100' : '' }}" aria-hidden="true">🕒</span>
                <span class="text-[9px]">Riwayat</span>
            </a>
            <a href="{{ route('profil') }}" class="flex min-w-0 flex-1 flex-col items-center gap-1 {{ Request::routeIs('profil') ? 'font-bold text-bksda-900' : 'text-slate-400' }}">
                <span class="rounded-xl p-1.5 text-lg {{ Request::routeIs('profil') ? 'bg-emerald-100' : '' }}" aria-hidden="true">👤</span>
                <span class="text-[9px]">Profil</span>
            </a>
        </nav>
    @endif

    @if (Request::routeIs('dashboard', 'petugas.dashboard', 'petugas.satwa', 'jadwal.index', 'dwh.index', 'riwayat', 'petugas.riwayat'))
        <script>
            (() => {
                let hasUnsavedChanges = false;

                document.addEventListener('input', (event) => {
                    if (event.target.closest('form')) {
                        hasUnsavedChanges = true;
                    }
                });

                document.addEventListener('change', (event) => {
                    if (event.target.closest('form')) {
                        hasUnsavedChanges = true;
                    }
                });

                const refresh = () => {
                    window.setTimeout(() => {
                        if (hasUnsavedChanges || window.sipakanPreventAutoRefresh) {
                            refresh();
                            return;
                        }

                        window.location.reload();
                    }, 5000);
                };

                refresh();
            })();
        </script>
    @endif
</body>
</html>
