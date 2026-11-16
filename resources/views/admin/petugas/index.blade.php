@extends('layouts.app')

@section('content')
<div class="space-y-5">
    @if(session('success'))
        <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800 shadow-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <header class="rounded-2xl bg-gradient-to-br from-emerald-900 to-emerald-950 p-5 text-white shadow-md">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-300">Administrasi akun</p>
        <h2 class="mt-1 text-lg font-bold">Kelola Petugas</h2>
        <p class="mt-1 text-xs leading-5 text-emerald-100/75">Setujui pendaftaran atau nonaktifkan akun. Akun nonaktif tidak menerima jadwal acak.</p>
    </header>

    <section class="grid grid-cols-2 gap-3" aria-label="Ringkasan akun petugas">
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-slate-500">Aktif</p>
            <p class="mt-1 text-2xl font-bold text-emerald-800">{{ $activeCount }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-amber-800">Menunggu</p>
            <p class="mt-1 text-2xl font-bold text-amber-900">{{ $pendingCount }}</p>
        </div>
        <div class="col-span-2 rounded-2xl border border-slate-100 bg-slate-50 p-4 shadow-sm">
            <p class="text-[10px] font-semibold text-slate-500">Nonaktif</p>
            <p class="mt-1 text-2xl font-bold text-slate-700">{{ $inactiveCount }}</p>
        </div>
    </section>

    <section aria-labelledby="petugas-list-heading">
        <div class="mb-3 flex items-center justify-between gap-3">
            <h3 id="petugas-list-heading" class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar pendaftar</h3>
            <span class="text-[10px] text-slate-400">{{ $petugas->total() }} akun</span>
        </div>

        <div class="space-y-3">
            @forelse($petugas as $user)
                <article class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h4 class="truncate text-sm font-bold text-slate-800">{{ $user->name }}</h4>
                            <p class="mt-1 break-all text-xs text-slate-500">{{ $user->email }}</p>
                        </div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-800' : ($user->approved_at ? 'bg-slate-100 text-slate-700' : 'bg-amber-50 text-amber-800') }}">
                            {{ $user->is_active ? 'Aktif' : ($user->approved_at ? 'Nonaktif' : 'Menunggu') }}
                        </span>
                    </div>
                    <div class="mt-3 rounded-xl bg-slate-50 px-3 py-2">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Tanggal daftar</p>
                        <p class="mt-1 text-xs font-semibold text-slate-700">{{ $user->created_at->format('d/m/Y') }}</p>
                    </div>
                    <form action="{{ route('admin.petugas.status', $user) }}" method="POST" class="mt-3">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                        <button type="submit" class="w-full rounded-xl border px-3 py-2.5 text-xs font-bold transition {{ $user->is_active ? 'border-rose-200 text-rose-700 hover:bg-rose-50' : 'border-emerald-200 bg-emerald-800 text-white hover:bg-emerald-900' }}">
                            {{ $user->is_active ? 'Nonaktifkan' : ($user->approved_at ? 'Aktifkan kembali' : 'Verifikasi / Aktifkan') }}
                        </button>
                    </form>
                </article>
            @empty
                <div class="rounded-2xl border border-slate-100 bg-white px-4 py-10 text-center text-xs font-medium text-slate-400 shadow-sm">
                    Belum ada petugas yang mendaftar.
                </div>
            @endforelse
        </div>

        @if($petugas->hasPages())
            <div class="mt-4">
                {{ $petugas->links() }}
            </div>
        @endif
    </section>
</div>
@endsection
