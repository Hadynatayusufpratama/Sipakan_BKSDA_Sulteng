@extends('layouts.app')

@section('content')
<div class="space-y-7">

    <!-- Header Banner DWH & IoT -->
    <div class="rounded-3xl border border-emerald-950/10 bg-gradient-to-br from-slate-950 via-emerald-950 to-emerald-900 p-6 text-white shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-emerald-300/20 bg-emerald-500/15 text-xl">
                📊
            </div>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Pelaporan & analitik</p>
                <h1 class="mt-1 text-lg font-black">Data Warehouse &amp; IoT</h1>
                <p class="mt-1 text-xs leading-5 text-emerald-100/75">Ringkasan tugas, penyelesaian, petugas aktif, dan tren pemberian pakan.</p>
            </div>
        </div>
    </div>

    <!-- Statistik Agregat (Data Warehouse Metrics) -->
    <div class="grid grid-cols-2 gap-3">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total tugas</span>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $totalJadwal }}</h2>
            <span class="mt-1 block text-[10px] font-semibold text-amber-700">{{ $jadwalPending }} menunggu</span>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Tugas selesai</span>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $jadwalSelesai }}</h2>
            <span class="mt-1 block text-[10px] font-semibold text-emerald-700">dari {{ $totalJadwal }} tugas</span>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Indeks kepatuhan</span>
            <h2 class="mt-2 text-3xl font-black text-emerald-700">{{ $persentaseKepatuhan }}%</h2>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Persentase tugas selesai" aria-valuenow="{{ $persentaseKepatuhan }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $persentaseKepatuhan }}%"></div>
            </div>
            <span class="mt-2 block text-[10px] font-semibold text-slate-500">Tugas selesai / total</span>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Petugas aktif</span>
            <h2 class="mt-2 text-3xl font-black text-slate-900">{{ $activePetugas }}</h2>
            <span class="mt-1 block text-[10px] font-semibold text-emerald-700">Aktif &amp; terverifikasi</span>
        </div>
    </div>

    <section class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Tren harian</h2>
                <p class="mt-1 text-[10px] text-slate-500">Tingkat penyelesaian tugas setiap hari.</p>
            </div>
            <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">7 hari</span>
        </div>
        <div class="space-y-3">
            @foreach($dailyTrend as $trend)
                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-2 text-[10px]">
                        <span class="font-semibold text-slate-600">{{ $trend->label }}</span>
                        <span class="text-right text-slate-500">
                            <strong class="text-slate-700">{{ $trend->completed_tasks }}/{{ $trend->total_tasks }}</strong> selesai
                            <span class="ml-1 font-bold text-emerald-700">{{ $trend->completion_rate }}%</span>
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Penyelesaian tanggal {{ $trend->date }}" aria-valuenow="{{ $trend->completion_rate }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $trend->completion_rate }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Tren mingguan</h2>
                <p class="mt-1 text-[10px] text-slate-500">Rekap penyelesaian per minggu.</p>
            </div>
            <span class="shrink-0 rounded-full bg-sky-50 px-2.5 py-1 text-[10px] font-bold text-sky-800">8 minggu</span>
        </div>
        <div class="space-y-3">
            @foreach($weeklyTrend as $trend)
                <div>
                    <div class="mb-1.5 flex items-center justify-between gap-2 text-[10px]">
                        <span class="font-semibold text-slate-600">{{ $trend->label }}</span>
                        <span class="text-right text-slate-500">
                            <strong class="text-slate-700">{{ $trend->completed_tasks }}/{{ $trend->total_tasks }}</strong> selesai
                            <span class="ml-1 font-bold text-emerald-700">{{ $trend->completion_rate }}%</span>
                        </span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-label="Penyelesaian minggu {{ $trend->label }}" aria-valuenow="{{ $trend->completion_rate }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="h-full rounded-full bg-sky-500 transition-all" style="width: {{ $trend->completion_rate }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- Simulated IoT readings; no sensor telemetry source is configured yet. -->
    <div class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Contoh Telemetri IoT Kandang</h3>
            <p class="text-[10px] text-slate-400 mt-1">Nilai berikut simulasi, bukan data sensor langsung.</p>
        </div>
        
        <div class="grid grid-cols-1 gap-3">
            <div class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50 p-4">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Suhu Kandang A</span>
                    <h5 class="text-base font-black text-slate-800">28.4 °C</h5>
                </div>
                <span class="text-xl">🌡️</span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50 p-4">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Kelembapan Udara</span>
                    <h5 class="text-base font-black text-slate-800">82 %</h5>
                </div>
                <span class="text-xl">💧</span>
            </div>
            <div class="flex items-center justify-between rounded-xl border border-slate-200/70 bg-slate-50 p-4">
                <div>
                    <span class="text-[10px] text-slate-400 font-bold uppercase">Kapasitas Wadah Pakan</span>
                    <h5 class="text-base font-black text-emerald-700">91.5 % (Normal)</h5>
                </div>
                <span class="text-xl">📡</span>
            </div>
        </div>
    </div>

    <!-- Agregat Tabel DWH -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-base font-bold text-slate-900">Distribusi tugas berdasarkan kandang</h3>
        <div class="space-y-2">
            @forelse($rekapKandang as $rekap)
                <div class="flex justify-between items-center py-2.5 border-b border-slate-100 text-xs">
                    <span class="font-bold text-slate-800">🏠 {{ $rekap->kandang ? 'Kandang '.$rekap->kandang : 'Kandang -' }}</span>
                    <span class="bg-emerald-50 text-emerald-800 px-2.5 py-1 rounded-lg font-bold border border-emerald-100 text-right">
                        {{ $rekap->tugas_selesai }} / {{ $rekap->total_tugas }} tugas selesai
                        <span class="block text-[10px] font-medium">{{ $rekap->total_tugas > 0 ? round(($rekap->tugas_selesai / $rekap->total_tugas) * 100) : 0 }}% kepatuhan kandang</span>
                    </span>
                </div>
            @empty
                <p class="text-xs text-slate-400 text-center py-4">Belum ada data agregat untuk diolah.</p>
            @endforelse
        </div>
    </div>

</div>
@endsection