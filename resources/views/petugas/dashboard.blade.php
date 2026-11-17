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

    @if($pendingTasksToday->isNotEmpty())
        <section aria-labelledby="daily-task-notice" class="space-y-3 rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Pengingat tugas hari ini</p>
                <h2 id="daily-task-notice" class="mt-1 text-sm font-bold text-slate-900">Halo {{ auth()->user()->name }}, Anda memiliki tugas memberi makan satwa hari ini.</h2>
                <p class="mt-1 text-xs leading-5 text-slate-600">Silakan lakukan scan QR di kandang untuk membuka formulir laporan.</p>
            </div>
            <ul class="space-y-2">
                @foreach($pendingTasksToday as $pendingTask)
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-amber-100 bg-white px-3 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-xs font-bold text-slate-800">{{ $pendingTask->satwa?->nama_satwa ?? 'Satwa' }}</p>
                            <p class="truncate text-[10px] text-slate-500">Kandang {{ $pendingTask->satwa?->kandang ?? '-' }}</p>
                        </div>
                        <a href="#task-{{ $pendingTask->id }}" class="shrink-0 rounded-lg bg-amber-600 px-3 py-2 text-[10px] font-bold text-white transition hover:bg-amber-700">
                            Scan QR
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @elseif($totalTugas > 0)
        <div role="status" class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-semibold text-emerald-800 shadow-sm">
            ✓ Semua tugas hari ini sudah selesai.
        </div>
    @else
        <div role="status" class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs font-medium text-slate-600 shadow-sm">
            Belum ada penugasan hari ini.
        </div>
    @endif

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
            <article id="task-{{ $tugas->id }}" class="scroll-mt-24 space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-lg shadow-slate-900/5">
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
                            <span aria-hidden="true">•</span>
                            Tanggal tugas: {{ $tugas->tanggal_tugas }}
                        </p>
                    </div>
                    <span class="shrink-0 text-[10px] font-bold px-2.5 py-1 rounded-full {{ $tugas->status === 'Selesai' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                        {{ $tugas->status }}
                    </span>
                </div>

                @if($tugas->status === 'Pending')
                    @php($checkpointAt = $checkpointScans[$tugas->id] ?? null)
                    @if(!$checkpointAt)
                        <section class="space-y-3 border-t border-slate-100 pt-3">
                            <div>
                                <p class="text-xs font-bold text-slate-800">Scan QR Kandang</p>
                                <p class="mt-1 text-[10px] leading-4 text-slate-500">Pindai kode QR kandang untuk mencatat waktu kedatangan dan membuka formulir laporan.</p>
                            </div>
                            <button
                                type="button"
                                data-checkpoint-scan
                                data-task-id="{{ $tugas->id }}"
                                data-reader-id="qr-reader-{{ $tugas->id }}"
                                data-status-id="qr-status-{{ $tugas->id }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-800 px-4 py-3 text-xs font-bold text-white transition hover:bg-emerald-900 focus:outline-none focus:ring-4 focus:ring-emerald-600/20"
                            >
                                Mulai Scan QR
                            </button>
                            <div id="qr-reader-{{ $tugas->id }}" class="hidden overflow-hidden rounded-xl"></div>
                            <p id="qr-status-{{ $tugas->id }}" role="status" class="text-[10px] leading-4 text-slate-500">Kamera memerlukan izin browser dan koneksi HTTPS atau localhost.</p>
                        </section>
                    @else
                        <div class="flex items-start gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-3 py-2.5 text-xs text-emerald-800">
                            <span aria-hidden="true">✓</span>
                            <p>Checkpoint tercatat: <time datetime="{{ $checkpointAt->toIso8601String() }}" class="font-bold">{{ $checkpointAt->format('d/m/Y H:i:s') }}</time></p>
                        </div>
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
                    @endif
                @else
                    <div class="flex flex-col gap-3 border-t border-slate-100 pt-3 min-[390px]:flex-row min-[390px]:items-start">
                        <div class="min-w-0 flex-1 space-y-2 text-xs text-slate-600">
                            <p class="whitespace-pre-line"><span class="font-semibold">Catatan:</span> {{ $tugas->catatan_petugas ?: 'Tidak ada catatan.' }}</p>
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-800">✓ Selesai Dilaporkan</span>
                        </div>
                        @if($tugas->bukti_foto)
                            <a href="{{ asset('storage/' . $tugas->bukti_foto) }}" target="_blank" rel="noopener noreferrer" class="group inline-flex w-fit shrink-0 items-center gap-2 rounded-lg border border-slate-200 bg-white p-2 text-xs font-semibold text-emerald-800 shadow-sm transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600/30">
                                <img src="{{ asset('storage/' . $tugas->bukti_foto) }}" alt="Bukti foto pakan {{ $tugas->satwa?->nama_satwa ?? '' }}" loading="lazy" class="h-16 w-16 rounded-lg border border-slate-200 object-cover">
                                <span class="max-w-24 leading-4 group-hover:underline">Lihat bukti foto</span>
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

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const checkpointEndpoint = @json(route('petugas.checkpoint'));
    const checkpointCsrfToken = @json(csrf_token());

    document.querySelectorAll('[data-checkpoint-scan]').forEach((button) => {
        const reader = document.getElementById(button.dataset.readerId);
        const status = document.getElementById(button.dataset.statusId);
        let scanner = null;
        let scanning = false;
        let processing = false;

        const closeScanner = async () => {
            if (!scanner) {
                window.sipakanPreventAutoRefresh = false;
                return;
            }

            if (scanning) {
                await scanner.stop();
            }

            await scanner.clear();
            scanner = null;
            scanning = false;
            window.sipakanPreventAutoRefresh = false;
            document.querySelectorAll('[data-checkpoint-scan]').forEach((otherButton) => {
                otherButton.disabled = false;
            });
            reader.classList.add('hidden');
        };

        button.addEventListener('click', async () => {
            if (scanning) {
                await closeScanner();
                status.textContent = 'Pemindai ditutup.';
                button.textContent = 'Mulai Scan QR';
                return;
            }

            if (typeof Html5Qrcode === 'undefined') {
                status.textContent = 'Library pemindai tidak dapat dimuat. Periksa koneksi internet lalu coba lagi.';
                return;
            }

            reader.classList.remove('hidden');
            status.textContent = 'Mengaktifkan kamera...';
            button.disabled = true;
            window.sipakanPreventAutoRefresh = true;
            document.querySelectorAll('[data-checkpoint-scan]').forEach((otherButton) => {
                if (otherButton !== button) {
                    otherButton.disabled = true;
                }
            });

            try {
                scanner = new Html5Qrcode(reader.id);
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 220, height: 220 } },
                    async (qrPayload) => {
                        if (processing) {
                            return;
                        }

                        processing = true;
                        status.textContent = 'QR terbaca. Memverifikasi checkpoint...';

                        try {
                            const response = await fetch(checkpointEndpoint, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': checkpointCsrfToken,
                                },
                                body: JSON.stringify({
                                    task_id: button.dataset.taskId,
                                    qr_payload: qrPayload,
                                }),
                            });
                            const result = await response.json();

                            if (!response.ok) {
                                throw new Error(result.message || 'QR tidak cocok dengan tugas hari ini.');
                            }

                            await closeScanner();
                            status.textContent = `Checkpoint tercatat pada ${result.scanned_at_display}. Membuka formulir laporan...`;
                            button.disabled = true;
                            button.textContent = 'Checkpoint Tercatat';
                            window.setTimeout(() => window.location.reload(), 700);
                        } catch (error) {
                            await closeScanner();
                            status.textContent = error.message || 'Checkpoint gagal diverifikasi.';
                            button.disabled = false;
                            button.textContent = 'Scan Ulang QR';
                            processing = false;
                        }
                    },
                    () => {}
                );

                scanning = true;
                button.disabled = false;
                button.textContent = 'Tutup Pemindai';
                status.textContent = 'Arahkan kamera ke QR checkpoint kandang.';
            } catch (error) {
                if (scanner) {
                    if (scanner.isScanning) {
                        await scanner.stop();
                    }
                    await scanner.clear();
                    scanner = null;
                    scanning = false;
                }

                window.sipakanPreventAutoRefresh = false;
                document.querySelectorAll('[data-checkpoint-scan]').forEach((otherButton) => {
                    otherButton.disabled = false;
                });
                reader.classList.add('hidden');
                status.textContent = `Kamera tidak dapat dibuka: ${error.message}`;
                button.disabled = false;
                button.textContent = 'Coba Scan Lagi';
            }
        });
    });
</script>
@endsection