@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <section class="rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-5 text-white shadow-lg">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Aktivitas lapangan</p>
        <h1 class="mt-1 text-lg font-black">Riwayat Laporan</h1>
        <p class="mt-1 text-xs leading-5 text-emerald-100/75">Catatan dan bukti dari tugas pakan yang telah diselesaikan.</p>
    </section>

    <div class="space-y-3">
        @forelse($riwayatTugas as $laporan)
            <article class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-lg shadow-slate-900/5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 class="font-black text-sm text-slate-800">🐾 {{ $laporan->satwa?->nama_satwa ?? 'Satwa' }}</h2>
                        <p class="text-xs text-slate-500 mt-1">
                            Kandang: {{ $laporan->satwa?->kandang ?? '-' }}
                            <span aria-hidden="true">•</span>
                            Tanggal tugas: {{ \Illuminate\Support\Carbon::parse($laporan->tanggal_tugas)->format('d M Y') }}
                        </p>
                        <p class="text-[10px] text-slate-400 mt-1">Petugas: {{ $laporan->petugas }}</p>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                        Selesai
                    </span>
                </div>

                <p class="text-[11px] text-slate-500">
                    Dilaporkan:
                    <time datetime="{{ $laporan->updated_at?->toIso8601String() }}">
                        {{ $laporan->updated_at?->format('d M Y, H:i') ?? '-' }}
                    </time>
                </p>

                <div class="pt-3 border-t border-slate-100">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Catatan Petugas</h3>
                    <p class="text-xs text-slate-700 mt-1 whitespace-pre-line">{{ $laporan->catatan_petugas ?: 'Tidak ada catatan.' }}</p>
                </div>

                @if($laporan->bukti_foto)
                    <a href="{{ Storage::disk('public')->url($laporan->bukti_foto) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-3 text-emerald-800 text-xs font-semibold">
                        <img src="{{ Storage::disk('public')->url($laporan->bukti_foto) }}" alt="Bukti foto pakan {{ $laporan->satwa?->nama_satwa ?? '' }}" loading="lazy" class="w-16 h-16 object-cover rounded-xl border border-slate-200">
                        Lihat bukti foto
                    </a>
                @endif
            </article>
        @empty
            <div class="bg-white p-8 rounded-2xl text-center text-slate-400 text-xs font-medium border border-slate-100 shadow-sm">
                Belum ada laporan pakan yang selesai.
            </div>
        @endforelse
    </div>

    @if($riwayatTugas->hasPages())
        <div class="px-1">
            {{ $riwayatTugas->links() }}
        </div>
    @endif
</div>
@endsection
