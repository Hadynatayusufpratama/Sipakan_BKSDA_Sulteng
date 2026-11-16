@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-6 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-16 h-56 w-56 rounded-full border border-white/10"></div>
        <div class="relative flex flex-col items-center gap-4 text-center">
            <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-20 w-20 rounded-2xl bg-white object-contain p-1.5 shadow-xl">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Profil administrator</p>
                <h1 class="mt-1 text-xl font-black">Administrator Lokal</h1>
                <p class="mt-1 text-sm text-emerald-100/75">BKSDA Sulawesi Tengah</p>
                <span class="mt-3 inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-400/10 px-3 py-1.5 text-[10px] font-semibold text-emerald-100">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                    Aktif &amp; terverifikasi
                </span>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Akun</p>
            <h2 class="mt-1 text-base font-bold text-slate-900">Informasi profil</h2>
        </div>
        <dl class="divide-y divide-slate-100">
            <div class="flex flex-col gap-1 py-3 text-sm">
                <dt class="text-xs font-medium text-slate-500">Email</dt>
                <dd class="font-semibold text-slate-800">admin.bksda@sulteng.go.id</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 text-sm">
                <dt class="text-xs font-medium text-slate-500">Unit kerja</dt>
                <dd class="font-semibold text-slate-800">BKSDA Sulawesi Tengah</dd>
            </div>
            <div class="flex flex-col gap-1 py-3 text-sm">
                <dt class="text-xs font-medium text-slate-500">Lokasi operasional</dt>
                <dd class="font-semibold text-slate-800">Palu, Sulawesi Tengah</dd>
            </div>
        </dl>
    </section>
</div>
@endsection