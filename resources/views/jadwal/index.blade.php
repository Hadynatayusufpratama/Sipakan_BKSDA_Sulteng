@extends('layouts.app')

@section('content')
<div class="space-y-7">

    <!-- Notifikasi -->
    @if(session('success'))
        <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm">
            ✨ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div role="alert" class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs font-semibold shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    <!-- Panel Kontrol Admin (Randomizer Engine) -->
    <div class="flex flex-col gap-5 overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-5 text-white shadow-lg">
        <div class="flex items-start gap-4">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-2xl" aria-hidden="true">🎲</span>
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Penjadwalan harian</p>
                <h2 class="mt-1 text-lg font-bold">Distribusi jadwal pakan</h2>
                <p class="mt-1 max-w-xl text-xs leading-5 text-emerald-100/75">Acak satwa aktif kepada petugas aktif. Laporan yang sudah selesai tetap tersimpan.</p>
            </div>
        </div>
        <form action="{{ route('jadwal.acak') }}" method="POST">
            @csrf
            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-400 px-5 py-3 text-sm font-bold text-emerald-950 shadow-lg shadow-black/10 transition hover:-translate-y-0.5 hover:bg-emerald-300 focus:outline-none focus:ring-4 focus:ring-emerald-200/30">
                Acak Jadwal
            </button>
        </form>
    </div>

    <!-- Daftar Tugas Petugas & Laporan -->
    <div>
        <div class="mb-4 flex flex-col gap-2">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Monitoring lapangan</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Jadwal &amp; laporan petugas</h2>
            </div>
            <div class="flex gap-2 text-[10px] font-semibold">
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-800">{{ $jadwal->where('status', 'Pending')->count() }} menunggu</span>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-800">{{ $jadwal->where('status', 'Selesai')->count() }} selesai</span>
            </div>
        </div>
        <div class="grid gap-4">
            @forelse($jadwal as $item)
                <article class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="inline-flex max-w-full truncate rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                Petugas: {{ $item->user?->name ?? $item->petugas }}
                            </span>
                            <h3 class="mt-2 text-sm font-bold text-slate-900">{{ $item->satwa->nama_satwa ?? 'Tidak diketahui' }}</h3>
                            <p class="mt-1 text-xs text-slate-500">Kandang {{ $item->satwa->kandang ?? '-' }} <span aria-hidden="true">•</span> {{ $item->tanggal_tugas }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status == 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            {{ $item->status }}
                        </span>
                    </div>

                    @if($item->status == 'Pending')
                        <p class="border-t border-slate-100 pt-3 text-xs text-slate-500">Menunggu laporan dari petugas yang ditugaskan.</p>
                    @else
                        <div class="pt-3 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                            <p><span class="font-semibold">Catatan:</span> {{ $item->catatan_petugas ?: 'Tidak ada catatan.' }}</p>
                            @if($item->bukti_foto)
                                <a href="{{ Storage::disk('public')->url($item->bukti_foto) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-emerald-800 font-semibold">
                                    <img src="{{ Storage::disk('public')->url($item->bukti_foto) }}" alt="Bukti foto pakan {{ $item->satwa?->nama_satwa ?? '' }}" loading="lazy" class="w-16 h-16 object-cover rounded-xl border border-slate-200">
                                    Lihat bukti foto
                                </a>
                            @endif
                            <p class="text-emerald-700 font-semibold">✓ Selesai Dilaporkan</p>
                        </article>
                    @endif
                </div>
            @empty
                <div class="bg-white p-8 rounded-2xl text-center text-slate-400 text-xs font-medium border border-slate-100 shadow-sm">
                    Belum ada jadwal pakan yang diacak. Silakan klik tombol "Acak Jadwal" di atas.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection