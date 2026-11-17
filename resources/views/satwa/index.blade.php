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
            <p class="font-bold">Data satwa belum dapat disimpan:</p>
            <ul class="list-disc pl-4 mt-1 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rounded-3xl border border-slate-200/80 bg-white p-5 shadow-lg">
        <div class="mb-5">
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Master data</p>
            <h2 class="mt-1 text-lg font-bold text-slate-900">Tambah satwa baru</h2>
            <p class="mt-1 text-xs leading-5 text-slate-500">Data aktif akan tersedia untuk penjadwalan petugas.</p>
        </div>
        <form action="{{ route('satwa.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label for="nama-satwa" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Satwa</label>
                <input id="nama-satwa" type="text" name="nama_satwa" value="{{ old('nama_satwa') }}" required maxlength="255" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="kategori-satwa" class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori</label>
                    <input id="kategori-satwa" type="text" name="kategori" value="{{ old('kategori') }}" required maxlength="255" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10" placeholder="Mamalia / Aves">
                </div>
                <div>
                    <label for="jumlah-satwa" class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah Ekor</label>
                    <input id="jumlah-satwa" type="number" name="jumlah_ekor" value="{{ old('jumlah_ekor') }}" min="1" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10">
                </div>
            </div>
            <div>
                <label for="kandang-satwa" class="block text-xs font-semibold text-slate-700 mb-1.5">Kandang / Lokasi</label>
                <input id="kandang-satwa" type="text" name="kandang" value="{{ old('kandang') }}" required maxlength="255" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10">
            </div>
            <div>
                <label for="status-satwa" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                <select id="status-satwa" name="status" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:border-emerald-600 focus:outline-none focus:ring-4 focus:ring-emerald-600/10">
                    <option value="Dalam pemantauan">Dalam pemantauan</option>
                    <option value="Sehat">Sehat</option>
                    <option value="Perawatan">Perawatan</option>
                </select>
            </div>
            <input type="hidden" name="is_active" value="0">
            <label for="satwa-active-new" class="flex items-start gap-2 text-xs text-slate-600">
                <input id="satwa-active-new" type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                <span>Satwa aktif dan dapat masuk jadwal pengacakan</span>
            </label>
            <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-emerald-800 to-emerald-700 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-900/15 transition hover:-translate-y-0.5 hover:from-emerald-900 hover:to-emerald-800 focus:outline-none focus:ring-4 focus:ring-emerald-600/20">
                Simpan Satwa
            </button>
        </form>
    </div>

    <div class="min-w-0">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-emerald-700">Master data</p>
                <h2 class="mt-1 text-lg font-bold text-slate-900">Daftar satwa</h2>
                <p class="mt-1 text-xs text-slate-500">Pantau status, lokasi, dan ketersediaan penjadwalan.</p>
            </div>
            <span class="shrink-0 rounded-full bg-white px-3 py-1.5 text-[10px] font-semibold text-slate-500 shadow-sm">{{ $satwa->count() }} data</span>
        </div>
        <div class="grid gap-3">
            @forelse($satwa as $item)
                <article class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:shadow-lg">
                    <div class="flex justify-between items-start gap-3">
                        <div class="min-w-0">
                            <h3 class="font-black text-slate-800 text-sm">{{ $item->nama_satwa }}</h3>
                            <p class="text-xs text-slate-500 mt-1">Kategori: {{ $item->kategori }} <span aria-hidden="true">•</span> Kandang: {{ $item->kandang }}</p>
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="inline-block text-[10px] bg-emerald-50 text-emerald-800 px-2.5 py-1 rounded-full font-semibold border border-emerald-100">{{ $item->status }}</span>
                                <span class="inline-block text-[10px] px-2.5 py-1 rounded-full font-semibold border {{ $item->is_active ? 'bg-teal-50 text-teal-800 border-teal-100' : 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                    {{ $item->is_active ? 'Aktif untuk jadwal' : 'Tidak aktif' }}
                                </span>
                            </div>
                        </div>
                        <div class="shrink-0 text-right bg-emerald-50 px-3 py-2 rounded-xl border border-emerald-100">
                            <span class="text-sm font-bold text-emerald-900 block">{{ $item->jumlah_ekor }}</span>
                            <span class="text-[10px] text-emerald-700">Ekor</span>
                        </div>
                    </div>

                    <section id="qr-sticker-{{ $item->id }}" class="qr-sticker space-y-3 rounded-2xl border border-emerald-100 bg-emerald-50/60 p-3">
                    <div class="flex items-center gap-3">
                        <div
                            id="qr-code-{{ $item->id }}"
                            data-qr-payload="{{ $qrPayloads[$item->id] }}"
                            class="flex h-32 w-32 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white p-2"
                            aria-label="QR checkpoint {{ $item->nama_satwa }}"
                        ></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700">QR Checkpoint Kandang</p>
                            <h4 class="mt-1 truncate text-sm font-bold text-slate-900">{{ $item->nama_satwa }}</h4>
                            <p class="mt-1 text-xs text-slate-600">Kandang {{ $item->kandang }}</p>
                            <p class="mt-1 text-[10px] text-slate-500">ID Satwa #{{ $item->id }}</p>
                        </div>
                    </div>
                    <button type="button" onclick="printQrSticker('qr-sticker-{{ $item->id }}')" class="no-print inline-flex w-full items-center justify-center rounded-xl bg-emerald-800 px-3 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-600/20">
                        Cetak QR
                    </button>
                    </section>

                    <details class="border-t border-slate-100 pt-3">
                        <summary class="cursor-pointer text-xs font-semibold text-emerald-800">Ubah data satwa</summary>
                        <form action="{{ route('satwa.update', $item) }}" method="POST" class="mt-3 space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="nama-satwa-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 mb-1.5">Nama Satwa</label>
                                <input id="nama-satwa-{{ $item->id }}" type="text" name="nama_satwa" value="{{ $item->nama_satwa }}" required maxlength="255" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="kategori-satwa-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 mb-1.5">Kategori</label>
                                    <input id="kategori-satwa-{{ $item->id }}" type="text" name="kategori" value="{{ $item->kategori }}" required maxlength="255" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                </div>
                                <div>
                                    <label for="jumlah-satwa-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 mb-1.5">Jumlah Ekor</label>
                                    <input id="jumlah-satwa-{{ $item->id }}" type="number" name="jumlah_ekor" value="{{ $item->jumlah_ekor }}" min="1" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
                                </div>
                            </div>
                            <div>
                                <label for="kandang-satwa-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 mb-1.5">Kandang / Lokasi</label>
                                <input id="kandang-satwa-{{ $item->id }}" type="text" name="kandang" value="{{ $item->kandang }}" required maxlength="255" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
                            </div>
                            <div>
                                <label for="status-satwa-{{ $item->id }}" class="block text-xs font-semibold text-slate-700 mb-1.5">Status</label>
                                <input id="status-satwa-{{ $item->id }}" type="text" name="status" value="{{ $item->status }}" required maxlength="255" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-emerald-600">
                            </div>
                            <input type="hidden" name="is_active" value="0">
                            <label for="satwa-active-{{ $item->id }}" class="flex items-start gap-2 text-xs text-slate-600">
                                <input id="satwa-active-{{ $item->id }}" type="checkbox" name="is_active" value="1" @checked($item->is_active) class="mt-0.5 h-4 w-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                <span>Satwa aktif dan dapat masuk jadwal pengacakan</span>
                            </label>
                            <button type="submit" class="w-full bg-bksda-900 text-white py-2.5 rounded-xl text-xs font-bold hover:bg-bksda-800 transition">Simpan Perubahan</button>
                        </form>
                    </details>

                    <form action="{{ route('satwa.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus data satwa ini beserta jadwal terkait?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full border border-rose-200 text-rose-700 py-2 rounded-xl text-xs font-semibold hover:bg-rose-50 transition">Hapus Satwa</button>
                    </form>
                </article>
            @empty
                <div class="bg-white p-8 rounded-2xl text-center text-slate-400 text-xs font-medium border border-slate-100 shadow-sm">
                    Belum ada data satwa yang ditambahkan.
                </div>
            @endforelse
        </div>
    </div>

</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.querySelectorAll('[data-qr-payload]').forEach((container) => {
        if (typeof QRCode === 'undefined') {
            container.textContent = 'QR tidak dapat dimuat';
            return;
        }

        new QRCode(container, {
            text: container.dataset.qrPayload,
            width: 112,
            height: 112,
            colorDark: '#022c22',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
    });

    function printQrSticker(stickerId) {
        const sticker = document.getElementById(stickerId);
        if (!sticker) {
            return;
        }

        document.querySelectorAll('.qr-print-active').forEach((element) => {
            element.classList.remove('qr-print-active');
        });
        sticker.classList.add('qr-print-active');
        document.body.classList.add('printing-qr');
        window.print();
        document.body.classList.remove('printing-qr');
        sticker.classList.remove('qr-print-active');
    }
</script>
<style>
    @media print {
        @page {
            margin: 12mm;
        }

        body.printing-qr * {
            visibility: hidden !important;
        }

        body.printing-qr .qr-print-active,
        body.printing-qr .qr-print-active * {
            visibility: visible !important;
        }

        body.printing-qr .qr-print-active {
            position: fixed !important;
            inset: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            width: 100% !important;
            height: 100% !important;
            border: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
        }

        body.printing-qr .qr-print-active .no-print {
            display: none !important;
        }
    }
</style>
@endsection