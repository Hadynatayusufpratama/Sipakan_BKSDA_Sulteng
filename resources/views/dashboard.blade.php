@extends('layouts.app')

@section('content')
<div class="space-y-7">
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-6 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-16 -top-20 h-64 w-64 rounded-full border border-white/10"></div>
        <div class="pointer-events-none absolute -bottom-24 right-24 h-52 w-52 rounded-full bg-emerald-300/10 blur-3xl"></div>
        <div class="relative flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-14 w-14 rounded-2xl bg-white object-contain p-1 shadow-xl">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-emerald-300">Panel Administrasi</p>
                    <h1 class="mt-1 text-xl font-black tracking-tight">Ringkasan Operasional</h1>
                    <p class="mt-1 text-xs text-emerald-100/75">Pantau satwa, penugasan, dan laporan lapangan.</p>
                </div>
            </div>
            <div class="inline-flex w-fit items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-xs font-medium text-emerald-50 backdrop-blur">
                <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                Tanggal {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </section>

    <section aria-labelledby="operational-summary">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <h2 id="operational-summary" class="text-base font-bold text-slate-900">Ikhtisar hari ini</h2>
                <p class="mt-1 text-xs text-slate-500">Ringkasan aktivitas pengelolaan dan perawatan satwa.</p>
            </div>
            <span class="rounded-full border border-emerald-100 bg-white px-3 py-1.5 text-[10px] font-semibold text-emerald-800 shadow-sm">Data terbaru</span>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">Total Satwa</p>
                    <span class="rounded-xl bg-emerald-50 p-2 text-lg" aria-hidden="true">🐾</span>
                </div>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ $totalSatwa ?? 0 }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Terdaftar di sistem</p>
            </article>
            <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">Kandang Pantau</p>
                    <span class="rounded-xl bg-sky-50 p-2 text-lg" aria-hidden="true">🏢</span>
                </div>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ $totalKandang ?? 0 }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Lokasi pemantauan</p>
            </article>
            <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">Jadwal Hari Ini</p>
                    <span class="rounded-xl bg-amber-50 p-2 text-lg" aria-hidden="true">📅</span>
                </div>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ count($jadwalHariIni ?? []) }}</p>
                <p class="mt-1 text-[11px] text-slate-500">Tugas terdistribusi</p>
            </article>
            <article class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold text-slate-500">Presensi Selesai</p>
                    <span class="rounded-xl bg-teal-50 p-2 text-lg" aria-hidden="true">✅</span>
                </div>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ $jadwalHariIni ? $jadwalHariIni->where('status', 'Selesai')->count() : 0 }}<span class="ml-1 text-base font-semibold text-slate-400">/ {{ count($jadwalHariIni ?? []) }}</span></p>
                <p class="mt-1 text-[11px] text-slate-500">Laporan petugas diterima</p>
            </article>
        </div>
    </section>

    <section aria-labelledby="quick-access">
        <div class="mb-4">
            <h2 id="quick-access" class="text-base font-bold text-slate-900">Akses cepat</h2>
            <p class="mt-1 text-xs text-slate-500">Kelola data dan tindak lanjuti aktivitas lapangan.</p>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('satwa.index') }}" class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
                <span class="mb-3 inline-flex rounded-xl bg-emerald-50 p-2.5 text-xl">🐾</span>
                <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-900">Data Satwa</span>
                <span class="mt-1 block text-[11px] text-slate-500">Kelola satwa & kandang</span>
            </a>
            <a href="{{ route('jadwal.index') }}" class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
                <span class="mb-3 inline-flex rounded-xl bg-sky-50 p-2.5 text-xl">📋</span>
                <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-900">Jadwal & Laporan</span>
                <span class="mt-1 block text-[11px] text-slate-500">Acak tugas dan tinjau</span>
            </a>
            <a href="{{ route('dwh.index') }}" class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
                <span class="mb-3 inline-flex rounded-xl bg-violet-50 p-2.5 text-xl">📊</span>
                <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-900">Analitik & DWH</span>
                <span class="mt-1 block text-[11px] text-slate-500">Pantau capaian tugas</span>
            </a>
            <a href="{{ route('admin.petugas.index') }}" class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
                <span class="mb-3 inline-flex rounded-xl bg-amber-50 p-2.5 text-xl">👥</span>
                <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-900">Manajemen Petugas</span>
                <span class="mt-1 block text-[11px] text-slate-500">Verifikasi akun lapangan</span>
            </a>
            <a href="{{ route('riwayat') }}" class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg">
                <span class="mb-3 inline-flex rounded-xl bg-slate-100 p-2.5 text-xl">🕒</span>
                <span class="block text-sm font-bold text-slate-800 group-hover:text-emerald-900">Log Aktivitas</span>
                <span class="mt-1 block text-[11px] text-slate-500">Riwayat laporan selesai</span>
            </a>
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4">
            <div>
                <h2 class="text-base font-bold text-slate-900">Jadwal pakan hari ini</h2>
                <p class="mt-1 text-xs text-slate-500">Status penugasan dan presensi petugas lapangan.</p>
            </div>
            <a href="{{ route('jadwal.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-800 transition hover:text-emerald-950">Lihat seluruh jadwal <span aria-hidden="true">→</span></a>
        </div>

        @if(isset($jadwalHariIni) && $jadwalHariIni->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($jadwalHariIni as $j)
                    <div class="flex items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50/80">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="hidden h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-lg" aria-hidden="true">🐾</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-800">{{ $j->satwa->nama_satwa ?? 'Satwa' }}</p>
                                <p class="mt-1 truncate text-xs text-slate-500">Petugas: {{ $j->user?->name ?? $j->petugas }}</p>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full px-3 py-1.5 text-[10px] font-bold {{ $j->status == 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $j->status }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <span class="text-3xl" aria-hidden="true">📅</span>
                <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada jadwal untuk hari ini</p>
                <p class="mt-1 text-xs text-slate-500">Buka Jadwal &amp; Laporan untuk mendistribusikan tugas aktif.</p>
                <a href="{{ route('jadwal.index') }}" class="mt-4 inline-flex rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-900">Buka jadwal</a>
            </div>
        @endif
    </section>
</div>
@endsection
