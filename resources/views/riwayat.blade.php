@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-4 rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-6 text-white shadow-lg">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Audit operasional</p>
            <h1 class="mt-1 text-xl font-black tracking-tight">Log aktivitas sistem</h1>
            <p class="mt-2 text-xs leading-5 text-emerald-100/75">Riwayat laporan pakan yang telah diselesaikan petugas lapangan.</p>
        </div>
        <span class="w-fit rounded-xl border border-white/15 bg-white/10 px-3 py-2 text-xs font-semibold text-emerald-50">{{ $riwayat->count() }} laporan</span>
    </section>

    <div class="grid gap-4">
        @forelse($riwayat as $item)
            <article class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">Pemberian pakan</p>
                        <h2 class="mt-1 truncate text-sm font-bold text-slate-900">{{ $item->satwa?->nama_satwa ?? 'Satwa dihapus' }}</h2>
                        <p class="mt-1 text-xs text-slate-500">Kandang: {{ $item->satwa?->kandang ?? '-' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">Selesai</span>
                </div>

                <div class="grid grid-cols-2 gap-3 rounded-xl bg-slate-50 p-3">
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Petugas</p>
                        <p class="mt-1 truncate text-xs font-semibold text-slate-700">{{ $item->petugas }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Dilaporkan</p>
                        <p class="mt-1 text-xs font-semibold text-slate-700">{{ $item->updated_at?->format('d M Y, H:i') ?? '-' }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-3 border-t border-slate-100 pt-3 min-[390px]:flex-row min-[390px]:items-start">
                    <div class="min-w-0 flex-1 space-y-2">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Catatan observasi</p>
                            <p class="mt-1 whitespace-pre-line text-xs leading-5 text-slate-700">{{ $item->catatan_petugas ?: 'Tidak ada catatan.' }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">✓ Selesai Dilaporkan</span>
                    </div>
                    @if($item->bukti_foto)
                        <a href="{{ asset('storage/' . $item->bukti_foto) }}" target="_blank" rel="noopener noreferrer" class="group inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 text-xs font-semibold text-emerald-800 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600/30">
                            <img src="{{ asset('storage/' . $item->bukti_foto) }}" alt="Bukti foto pakan {{ $item->satwa?->nama_satwa ?? '' }}" loading="lazy" class="h-16 w-16 rounded-lg border border-slate-200 object-cover">
                            <span class="max-w-24 leading-4 group-hover:underline">Lihat bukti foto</span>
                        </a>
                    @endif
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200/80 bg-white px-6 py-14 text-center shadow-sm">
                <span class="text-3xl" aria-hidden="true">🕒</span>
                <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada riwayat laporan</p>
                <p class="mt-1 text-xs text-slate-500">Laporan yang sudah selesai akan tercatat di sini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
