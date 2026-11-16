@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-6 text-white text-center shadow-lg">
        <div class="pointer-events-none absolute -right-8 -top-12 h-44 w-44 rounded-full border border-white/10"></div>
        <div class="relative">
            <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="mx-auto mb-4 h-20 w-20 rounded-2xl bg-white object-contain p-1.5 shadow-xl">
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Profil Petugas</p>
            <h1 class="mt-1 text-lg font-black">Petugas Lapangan</h1>
            <p class="mt-1 text-xs text-emerald-100/75">Sektor Pemeliharaan Satwa</p>
            <span class="mt-4 inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1.5 text-[10px] font-semibold text-emerald-100">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                Portal aktif
            </span>
        </div>
    </section>

    <section class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Informasi penugasan</p>
            <h2 class="mt-1 text-sm font-bold text-slate-900">Unit kerja</h2>
        </div>
        <dl class="divide-y divide-slate-100">
            <div class="flex items-center justify-between gap-4 py-3 text-xs">
                <dt class="text-slate-500">Instansi</dt>
                <dd class="text-right font-semibold text-slate-800">BKSDA Sulawesi Tengah</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3 text-xs">
                <dt class="text-slate-500">Unit kerja</dt>
                <dd class="text-right font-semibold text-slate-800">Sektor Pemeliharaan Satwa</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3 text-xs">
                <dt class="text-slate-500">Area kerja</dt>
                <dd class="text-right font-semibold text-slate-800">Palu, Sulteng</dd>
            </div>
            <div class="flex items-center justify-between gap-4 py-3 text-xs">
                <dt class="text-slate-500">Status portal</dt>
                <dd class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    Siap digunakan
                </dd>
            </div>
        </dl>
    </section>
</div>
@endsection
