@extends('layouts.app')

@section('content')
<style>
    @media print {
        @page {
            margin: 14mm;
        }

        body > .relative.mx-auto.max-w-md {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            min-height: 0 !important;
            overflow: visible !important;
            box-shadow: none !important;
        }

        body > .relative.mx-auto.max-w-md > main {
            display: block !important;
            width: 100% !important;
            padding: 0 !important;
        }

        body * {
            visibility: hidden !important;
        }

        #monthly-report-print,
        #monthly-report-print * {
            visibility: visible !important;
        }

        #monthly-report-print {
            display: block !important;
            position: static !important;
            width: 100%;
            padding: 0;
        }

        #monthly-report-print .print-report-table {
            width: 100%;
            border-collapse: collapse;
        }

        #monthly-report-print th,
        #monthly-report-print td {
            border: 1px solid #94a3b8;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        #monthly-report-print a::after {
            content: " (" attr(href) ")";
            overflow-wrap: anywhere;
            font-size: 8pt;
        }
    }
</style>

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

    @if(session('waDistributionLinks'))
        <section role="status" aria-label="Tautan pengingat WhatsApp" class="space-y-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm print:hidden">
            <div>
                <h2 class="text-sm font-bold text-emerald-950">Jadwal berhasil dibagikan</h2>
                <p class="mt-1 text-xs text-emerald-800">Pilih tautan untuk membuka WhatsApp dengan pesan pengingat yang sudah disiapkan.</p>
            </div>
            @forelse(session('waDistributionLinks') as $waTask)
                <div class="flex flex-col justify-between gap-2 rounded-xl border border-emerald-100 bg-white p-3 sm:flex-row sm:items-center">
                    <p class="min-w-0 text-xs text-slate-700">
                        <span class="font-semibold">{{ $waTask['petugas'] }}</span>
                        <span aria-hidden="true">·</span>
                        {{ $waTask['satwa'] }}
                    </p>
                    @if($waTask['url'])
                        <a href="{{ $waTask['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-emerald-700 px-3 py-2 text-[10px] font-bold text-white hover:bg-emerald-800">
                            Kirim WA ke Petugas
                        </a>
                    @else
                        <a href="{{ route('admin.petugas.index') }}" class="text-[10px] font-semibold text-amber-800 underline">
                            Tambahkan nomor HP Petugas
                        </a>
                    @endif
                </div>
            @empty
                <p class="rounded-xl bg-white p-3 text-xs text-slate-600">Tidak ada tugas baru yang perlu dikirimkan.</p>
            @endforelse
        </section>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm print:hidden">
        <form action="{{ route('jadwal.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            <div class="min-w-0 flex-1">
                <label for="filter-bulan" class="mb-1.5 block text-xs font-semibold text-slate-700">Filter laporan per bulan</label>
                <input
                    id="filter-bulan"
                    type="month"
                    name="filter_bulan"
                    value="{{ $filterBulan }}"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-800 focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10"
                >
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-emerald-800 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-900 sm:flex-none">
                    Tampilkan
                </button>
                <button type="button" onclick="window.print()" class="flex-1 rounded-xl border border-emerald-800 px-4 py-2.5 text-xs font-bold text-emerald-900 transition hover:bg-emerald-50 sm:flex-none">
                    Cetak Laporan Bulanan
                </button>
            </div>
        </form>
    </section>

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
                <p class="mt-1 text-xs text-slate-500">{{ $filterBulanLabel }}</p>
            </div>
            <div class="flex flex-wrap gap-2 text-[10px] font-semibold">
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-700">{{ $totalTugas }} tugas</span>
                <span class="rounded-full bg-amber-50 px-3 py-1.5 text-amber-800">{{ $totalPending }} belum diberi makan</span>
                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-emerald-800">{{ $totalSelesai }} sudah diberi makan · {{ $persentaseSelesai }}%</span>
            </div>
        </div>
        <div class="space-y-4">
            @forelse($jadwal as $item)
                <article class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:shadow-lg">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <span class="inline-flex max-w-full truncate rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-600">
                                Petugas: {{ $item->user?->name ?? $item->petugas }}
                            </span>
                            <h3 class="mt-2 text-sm font-bold text-slate-900">{{ $item->satwa->nama_satwa ?? 'Tidak diketahui' }}</h3>
                            <p class="mt-1 text-xs text-slate-500">Kandang {{ $item->satwa->kandang ?? '-' }} <span aria-hidden="true">•</span> {{ $item->tanggal_tugas }}</p>
                            @if($item->status === 'Selesai' && $item->updated_at)
                                <p class="mt-1 text-[11px] text-slate-600">
                                    <span class="font-semibold">Dilaporkan:</span>
                                    <time datetime="{{ $item->updated_at->toIso8601String() }}" class="font-medium text-emerald-800">{{ $item->updated_at->format('Y-m-d H:i:s') }}</time>
                                </p>
                            @endif
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold {{ $item->status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                            <span aria-hidden="true">{{ $item->status === 'Selesai' ? '✓' : '!' }}</span>
                            {{ $item->status === 'Selesai' ? 'Sudah Diberi Makan' : 'Belum Diberi Makan' }}
                        </span>
                    </div>

                    @if($item->status == 'Pending')
                        <div class="flex flex-col gap-3 border-t border-slate-100 pt-3 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-xs text-slate-500">Menunggu laporan dari petugas yang ditugaskan.</p>
                            @if($whatsappLinks[$item->id] ?? false)
                                <a href="{{ $whatsappLinks[$item->id] }}" target="_blank" rel="noopener noreferrer" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-800 sm:w-auto">
                                    Kirim WA ke Petugas
                                </a>
                            @else
                                <a href="{{ route('admin.petugas.index') }}" class="text-[10px] font-semibold text-amber-800 underline">
                                    Nomor WA belum diatur
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="flex flex-col gap-3 border-t border-slate-100 pt-3 min-[390px]:flex-row min-[390px]:items-start">
                            <div class="min-w-0 flex-1 space-y-2 text-xs text-slate-600">
                                <p class="whitespace-pre-line"><span class="font-semibold">Catatan:</span> {{ $item->catatan_petugas ?: 'Tidak ada catatan.' }}</p>
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">✓ Selesai Dilaporkan</span>
                            </div>
                            @if($item->bukti_foto)
                                <a href="{{ asset('storage/' . $item->bukti_foto) }}" target="_blank" rel="noopener noreferrer" class="group inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 text-xs font-semibold text-emerald-800 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600/30">
                                    <img src="{{ asset('storage/' . $item->bukti_foto) }}" alt="Bukti foto pakan {{ $item->satwa?->nama_satwa ?? '' }}" loading="lazy" class="h-16 w-16 rounded-lg border border-slate-200 object-cover">
                                    <span class="max-w-24 leading-4 group-hover:underline">Lihat bukti foto</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </article>
            @empty
                <div class="bg-white p-8 rounded-2xl text-center text-slate-400 text-xs font-medium border border-slate-100 shadow-sm">
                    Belum ada jadwal pakan yang diacak. Silakan klik tombol "Acak Jadwal" di atas.
                </div>
            @endforelse
        </div>
    </div>

</div>

<section id="monthly-report-print" class="hidden">
    <header class="mb-6 border-b-2 border-emerald-800 pb-4">
        <p class="text-sm font-bold uppercase tracking-widest text-emerald-900">BKSDA Sulteng</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900">Laporan Jadwal Pakan Bulanan</h1>
        <p class="mt-1 text-sm text-slate-600">Periode: {{ $filterBulanLabel }}</p>
        <p class="mt-1 text-sm text-slate-600">
            Total {{ $totalTugas }} tugas · {{ $totalSelesai }} sudah diberi makan · {{ $totalPending }} belum diberi makan
        </p>
    </header>

    <table class="print-report-table text-xs">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Satwa / Kandang</th>
                <th>Petugas</th>
                <th>Status pakan</th>
                <th>Catatan</th>
                <th>Bukti foto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jadwal as $item)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($item->tanggal_tugas)->format('d/m/Y') }}</td>
                    <td>{{ $item->satwa?->nama_satwa ?? 'Tidak diketahui' }}<br>{{ $item->satwa?->kandang ?? '-' }}</td>
                    <td>{{ $item->user?->name ?? $item->petugas }}</td>
                    <td>{{ $item->status === 'Selesai' ? 'Sudah Diberi Makan' : 'Belum Diberi Makan' }}</td>
                    <td>{{ $item->catatan_petugas ?: '-' }}</td>
                    <td>
                        @if($item->bukti_foto)
                            <a href="{{ asset('storage/' . $item->bukti_foto) }}">{{ basename($item->bukti_foto) }}</a>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Tidak ada jadwal pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection