@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <section class="rounded-2xl bg-gradient-to-r from-emerald-900 to-emerald-950 p-4 text-white shadow-sm">
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-300">Data satwa</p>
        <h1 class="mt-1 text-base font-black">Satwa Aktif</h1>
        <p class="mt-1 text-xs leading-5 text-emerald-100/75">Daftar satwa aktif yang dikelola Admin dan dapat dijadwalkan untuk pemberian pakan.</p>
    </section>

    <div class="flex items-center justify-between px-1">
        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Satwa</h2>
        <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">{{ $satwa->count() }} aktif</span>
    </div>

    <div class="space-y-3">
        @forelse($satwa as $item)
            <article class="space-y-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="text-sm font-black text-slate-800">{{ $item->nama_satwa }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $item->kategori }}</p>
                    </div>
                    <div class="shrink-0 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2 text-right">
                        <span class="block text-sm font-bold text-emerald-900">{{ $item->jumlah_ekor }}</span>
                        <span class="text-[10px] text-emerald-700">Ekor</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3">
                    <p class="text-xs text-slate-600">
                        <span class="font-semibold text-slate-700">Kandang:</span>
                        {{ $item->kandang }}
                    </p>
                    <span class="rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-800">
                        {{ $item->status }}
                    </span>
                </div>

                @if($item->catatan)
                    <p class="whitespace-pre-line rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-600">
                        <span class="font-semibold text-slate-700">Catatan Admin:</span>
                        {{ $item->catatan }}
                    </p>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-100 bg-white p-8 text-center shadow-sm">
                <span class="text-2xl" aria-hidden="true">🐾</span>
                <p class="mt-2 text-sm font-semibold text-slate-700">Belum ada satwa aktif</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">Satwa yang diaktifkan Admin akan tampil di halaman ini.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
