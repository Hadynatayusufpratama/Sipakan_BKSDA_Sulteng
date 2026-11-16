@extends('layouts.app')

@section('content')
<div class="space-y-5">
    <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-950 via-emerald-900 to-green-950 p-5 text-white shadow-lg">
        <div class="pointer-events-none absolute -right-10 -top-14 h-44 w-44 rounded-full border border-white/10"></div>
        <div class="relative flex items-center gap-3.5">
            <img src="{{ asset('images/bksda-sulteng-logo.jpg') }}" alt="Logo BKSDA Sulawesi Tengah" class="h-12 w-12 shrink-0 rounded-2xl bg-white object-contain p-1 shadow-lg">
            <div class="min-w-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-300">Portal Petugas Lapangan</p>
                <h1 class="mt-1 truncate text-base font-black">{{ auth()->user()->name }}</h1>
                <p class="mt-0.5 text-xs text-emerald-100/75">Ringkasan tugas pakan hari ini</p>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm">
            ✨ {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800 shadow-sm">
            <p class="font-bold">Laporan belum dapat dikirim:</p>
            <ul class="list-disc pl-4 mt-1 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-3 gap-2.5">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm">
            <span class="text-[9px] font-bold uppercase leading-4 text-slate-400">Satwa aktif</span>
            <h2 class="mt-1 text-2xl font-black text-emerald-800">{{ $totalSatwa }}</h2>
            <p class="text-[9px] text-slate-500">Data admin</p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm">
            <span class="text-[9px] font-bold uppercase leading-4 text-slate-400">Total tugas</span>
            <h2 class="mt-1 text-2xl font-black text-slate-800">{{ $totalTugas }}</h2>
            <p class="text-[9px] text-slate-500">Hari ini</p>
        </div>
        <div class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm">
            <span class="text-[9px] font-bold uppercase leading-4 text-slate-400">Selesai</span>
            <h2 class="mt-1 text-2xl font-black text-emerald-700">{{ $tugasSelesai }}</h2>
            <p class="text-[9px] text-slate-500">dari {{ $totalTugas }} tugas</p>
        </div>
    </div>

    <div id="tugas" class="space-y-3 scroll-mt-20">
        <div class="flex items-end justify-between gap-2">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Eksekusi lapangan</p>
                <h2 class="mt-1 text-base font-bold text-slate-900">Daftar Penugasan</h2>
            </div>
            <a href="{{ route('petugas.satwa') }}" class="shrink-0 rounded-xl border border-emerald-100 bg-white px-3 py-2 text-[10px] font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-50">Lihat satwa</a>
        </div>

        @forelse($jadwalTugas as $tugas)
            <article class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-lg shadow-slate-900/5">
                <div class="flex justify-between items-start">
                    <div class="min-w-0 pr-2">
                        <span class="text-[10px] font-bold uppercase bg-emerald-50 text-emerald-800 px-2 py-0.5 rounded-md border border-emerald-100">
                            Petugas: {{ $tugas->user?->name ?? $tugas->petugas }}
                        </span>
                        <h4 class="font-black text-slate-800 text-sm mt-1.5">🐾 {{ $tugas->satwa?->nama_satwa ?? 'Satwa' }}</h4>
                        <p class="mt-1 text-[11px] text-slate-500">
                            {{ $tugas->satwa?->kategori ?? 'Satwa' }}
                            <span aria-hidden="true">•</span>
                            Kandang: {{ $tugas->satwa?->kandang ?? '-' }}
                            <span aria-hidden="true">•</span>
                            Jumlah: {{ $tugas->satwa?->jumlah_ekor ?? '-' }} ekor
                        </p>
                    </div>
                    <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $tugas->status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $tugas->status }}
                    </span>
                </div>

                @if($tugas->status === 'Pending')
                    <form action="{{ route('petugas.laporan', $tugas->id) }}" method="POST" enctype="multipart/form-data" class="pt-3 border-t border-slate-100 space-y-3">
                        @csrf
                        <div>
                            <label for="catatan-{{ $tugas->id }}" class="block text-xs font-semibold text-slate-700 mb-1.5">Catatan kondisi pakan/satwa</label>
                            <textarea id="catatan-{{ $tugas->id }}" name="catatan_petugas" rows="3" maxlength="2000" required placeholder="Tuliskan kondisi pakan atau satwa..." class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-xs leading-5 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10">{{ old('catatan_petugas') }}</textarea>
                        </div>
                        <div>
                            <label for="bukti-{{ $tugas->id }}" class="mb-1.5 block text-xs font-semibold text-slate-700">Bukti foto <span class="font-normal text-slate-500">(JPG, PNG, WEBP; maks. 5 MB)</span></label>
                            <div class="rounded-2xl border-2 border-dashed border-emerald-200 bg-emerald-50/50 p-3 transition hover:border-emerald-400">
                                <div class="flex items-center gap-2 text-[10px] font-medium text-slate-500">
                                    <span class="text-lg" aria-hidden="true">📷</span>
                                    Pilih foto kondisi satwa atau pemberian pakan
                                </div>
                                <input id="bukti-{{ $tugas->id }}" type="file" name="bukti_foto" accept="image/jpeg,image/png,image/webp" required class="mt-2 block w-full cursor-pointer text-[10px] text-slate-500 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-800 file:px-3 file:py-2 file:text-[10px] file:font-semibold file:text-white hover:file:bg-emerald-900">
                            </div>
                        </div>
                        <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-emerald-800 to-emerald-700 px-4 py-3 text-xs font-bold text-white shadow-lg shadow-emerald-900/15 transition hover:-translate-y-0.5 hover:from-emerald-900 hover:to-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-600/20">
                            Kirim Laporan & Tandai Selesai
                        </button>
                    </form>
                @else
                    <div class="pt-3 border-t border-slate-100 text-xs text-slate-600 space-y-2">
                        <p><span class="font-semibold">Catatan:</span> {{ $tugas->catatan_petugas ?: 'Tidak ada catatan.' }}</p>
                        @if($tugas->bukti_foto)
                            <a href="{{ Storage::disk('public')->url($tugas->bukti_foto) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-emerald-800 font-semibold">
                                <img src="{{ Storage::disk('public')->url($tugas->bukti_foto) }}" alt="Bukti foto pakan {{ $tugas->satwa?->nama_satwa ?? '' }}" class="w-16 h-16 object-cover rounded-xl border border-slate-200">
                                Lihat bukti foto
                            </a>
                        @endif
                    </div>
                @endif
            </article>
        @empty
            <div class="rounded-2xl border border-slate-200/80 bg-white p-8 text-center shadow-sm">
                <span class="text-3xl" aria-hidden="true">📅</span>
                <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada penugasan hari ini</p>
                <p class="mt-1 text-xs leading-5 text-slate-500">Jadwal dari Admin akan muncul otomatis setelah didistribusikan.</p>
                <a href="{{ route('petugas.satwa') }}" class="mt-4 inline-flex rounded-xl bg-emerald-50 px-4 py-2.5 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-100">Lihat daftar satwa</a>
            </div>
        @endforelse
    </div>

</div>
@endsection