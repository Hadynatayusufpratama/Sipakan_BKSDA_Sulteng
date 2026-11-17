<?php

namespace App\Http\Controllers;

use App\Models\Satwa;
use App\Models\JadwalPakan;
use App\Models\User;
use App\Services\SatwaCheckpointQr;
use App\Services\WhatsAppLinkGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SatwaController extends Controller
{
    public function index(SatwaCheckpointQr $checkpointQr)
    {
        $satwa = Satwa::all();
        $qrPayloads = $satwa->mapWithKeys(
            fn (Satwa $item) => [$item->id => $checkpointQr->payloadFor($item)]
        );

        return view('satwa.index', compact('satwa', 'qrPayloads'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_satwa' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah_ekor' => 'required|integer|min:1',
            'kandang' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : true;

        Satwa::create($validated);

        return redirect()->back()->with('success', 'Data satwa berhasil ditambahkan!');
    }

    public function update(Request $request, Satwa $satwa)
    {
        $validated = $request->validate([
            'nama_satwa' => 'required|string|max:255',
            'kategori' => 'required|string|max:255',
            'jumlah_ekor' => 'required|integer|min:1',
            'kandang' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $satwa->update($validated);

        return redirect()->back()->with('success', 'Data satwa berhasil diperbarui!');
    }

    public function destroy(Satwa $satwa)
    {
        $photoPaths = DB::transaction(function () use ($satwa) {
            $photoPaths = JadwalPakan::where('satwa_id', $satwa->id)
                ->whereNotNull('bukti_foto')
                ->pluck('bukti_foto')
                ->all();

            $satwa->delete();

            return $photoPaths;
        });

        foreach ($photoPaths as $photoPath) {
            if (! Storage::disk('public')->delete($photoPath)) {
                Log::warning('Bukti laporan tidak dapat dihapus setelah data satwa dihapus.', [
                    'path' => $photoPath,
                    'satwa_id' => $satwa->id,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Data satwa berhasil dihapus.');
    }

    public function dashboard()
    {
        $totalSatwa = Satwa::count();
        $totalKandang = Satwa::distinct()->count('kandang');
        
        $jadwalHariIni = JadwalPakan::with(['satwa', 'user'])
            ->whereDate('tanggal_tugas', now()->toDateString())
            ->get();
        
        return view('dashboard', compact('totalSatwa', 'totalKandang', 'jadwalHariIni'));
    }

    public function riwayat()
    {
        $riwayat = JadwalPakan::with('satwa')
            ->where('status', 'Selesai')
            ->orderByDesc('updated_at')
            ->get();

        return view('riwayat', compact('riwayat'));
    }

    public function profil()
    {
        return view('profil');
    }

    // --- FITUR ADMIN & USER (JADWAL & LAPORAN) ---
    public function jadwalIndex(Request $request, WhatsAppLinkGenerator $whatsAppLinkGenerator)
    {
        $validated = $request->validate([
            'filter_bulan' => 'nullable|date_format:Y-m',
        ]);

        $filterBulan = $validated['filter_bulan'] ?? now()->format('Y-m');
        $awalBulan = Carbon::createFromFormat('!Y-m', $filterBulan)->startOfMonth();
        $akhirBulan = $awalBulan->copy()->endOfMonth();

        $jadwal = JadwalPakan::with(['satwa', 'user'])
            ->whereBetween('tanggal_tugas', [$awalBulan->toDateString(), $akhirBulan->toDateString()])
            ->orderByDesc('tanggal_tugas')
            ->orderByDesc('id')
            ->get();

        $totalTugas = $jadwal->count();
        $totalSelesai = $jadwal->where('status', 'Selesai')->count();
        $totalPending = $jadwal->where('status', 'Pending')->count();
        $persentaseSelesai = $totalTugas > 0 ? round(($totalSelesai / $totalTugas) * 100) : 0;
        $filterBulanLabel = $awalBulan->locale('id')->translatedFormat('F Y');
        $whatsappLinks = $jadwal->mapWithKeys(
            fn (JadwalPakan $item) => [$item->id => $whatsAppLinkGenerator->forTask($item)]
        );

        return view('jadwal.index', compact(
            'jadwal',
            'filterBulan',
            'filterBulanLabel',
            'totalTugas',
            'totalSelesai',
            'totalPending',
            'persentaseSelesai',
            'whatsappLinks'
        ));
    }

    public function acakJadwal(WhatsAppLinkGenerator $whatsAppLinkGenerator)
    {
        $tanggalTugas = now()->toDateString();

        $result = DB::transaction(function () use ($tanggalTugas) {
            $semuaSatwa = Satwa::query()
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();
            $petugasList = User::query()
                ->where('role', 'petugas')
                ->where('is_active', true)
                ->whereNotNull('approved_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'name']);

            if ($semuaSatwa->isEmpty() || $petugasList->isEmpty()) {
                return [
                    'error' => $semuaSatwa->isEmpty()
                        ? 'Belum ada satwa aktif untuk diacak jadwalnya.'
                        : 'Belum ada akun Petugas aktif dan terverifikasi untuk menerima jadwal.',
                ];
            }

            $satwaSelesaiHariIni = JadwalPakan::query()
                ->whereDate('tanggal_tugas', $tanggalTugas)
                ->where('status', 'Selesai')
                ->pluck('satwa_id')
                ->unique();

            JadwalPakan::query()
                ->whereDate('tanggal_tugas', $tanggalTugas)
                ->where('status', 'Pending')
                ->delete();

            $petugasList = $petugasList->shuffle()->values();
            $satwaUntukDijadwalkan = $semuaSatwa
                ->reject(fn (Satwa $satwa) => $satwaSelesaiHariIni->contains($satwa->id))
                ->shuffle()
                ->values();

            foreach ($satwaUntukDijadwalkan as $index => $satwa) {
                $petugas = $petugasList[$index % $petugasList->count()];

                JadwalPakan::create([
                    'satwa_id' => $satwa->id,
                    'user_id' => $petugas->id,
                    'petugas' => $petugas->name,
                    'tanggal_tugas' => $tanggalTugas,
                    'status' => 'Pending',
                ]);
            }

            return [];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        $waDistributionLinks = JadwalPakan::with(['satwa', 'user'])
            ->where('tanggal_tugas', $tanggalTugas)
            ->where('status', 'Pending')
            ->orderBy('id')
            ->get()
            ->map(fn (JadwalPakan $item) => [
                'task_id' => $item->id,
                'petugas' => $item->user?->name ?? $item->petugas,
                'satwa' => $item->satwa?->nama_satwa ?? 'Satwa',
                'url' => $whatsAppLinkGenerator->forTask($item),
            ])
            ->all();

        return redirect()->back()
            ->with('success', 'Berhasil mengacak dan mendistribusikan jadwal pakan petugas!')
            ->with('waDistributionLinks', $waDistributionLinks);
    }

    // --- FITUR DATA WAREHOUSE & ANALITIK IOT ---
    public function dwhIndex()
    {
        $totalSatwa = Satwa::count();
        $jadwalMetrics = JadwalPakan::query()
            ->selectRaw('COUNT(*) as total_jadwal')
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as jadwal_selesai',
                ['Selesai']
            )
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as jadwal_pending',
                ['Pending']
            )
            ->first();

        $totalJadwal = (int) $jadwalMetrics->total_jadwal;
        $jadwalSelesai = (int) $jadwalMetrics->jadwal_selesai;
        $jadwalPending = (int) $jadwalMetrics->jadwal_pending;
        $persentaseKepatuhan = $totalJadwal > 0 ? round(($jadwalSelesai / $totalJadwal) * 100) : 0;
        $activePetugas = User::query()
            ->where('role', 'petugas')
            ->where('is_active', true)
            ->whereNotNull('approved_at')
            ->count();

        $today = now()->startOfDay();
        $dailyStart = $today->copy()->subDays(6);
        $weeklyStart = $today->copy()->startOfWeek(Carbon::MONDAY)->subWeeks(7);
        $trendRows = DB::table('jadwal_pakan')
            ->select('tanggal_tugas as task_date')
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_tasks',
                ['Selesai']
            )
            ->whereBetween('tanggal_tugas', [$weeklyStart->toDateString(), $today->toDateString()])
            ->groupBy('tanggal_tugas')
            ->orderBy('tanggal_tugas')
            ->get();

        $dailyMetrics = $trendRows->keyBy(fn ($row) => (string) $row->task_date);
        $dailyTrend = collect();
        for ($date = $dailyStart->copy(); $date->lte($today); $date->addDay()) {
            $dateKey = $date->toDateString();
            $metrics = $dailyMetrics->get($dateKey);
            $totalTasks = (int) ($metrics->total_tasks ?? 0);
            $completedTasks = (int) ($metrics->completed_tasks ?? 0);

            $dailyTrend->push((object) [
                'date' => $dateKey,
                'label' => $date->format('d/m'),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
            ]);
        }

        $weeklyMetrics = $trendRows->groupBy(
            fn ($row) => Carbon::parse($row->task_date)->startOfWeek(Carbon::MONDAY)->toDateString()
        );
        $weeklyTrend = collect();
        for ($week = 0; $week < 8; $week++) {
            $weekStart = $weeklyStart->copy()->addWeeks($week);
            $weekEnd = $weekStart->copy()->addDays(6);
            if ($weekEnd->gt($today)) {
                $weekEnd = $today->copy();
            }

            $metrics = $weeklyMetrics->get($weekStart->toDateString(), collect());
            $totalTasks = (int) $metrics->sum('total_tasks');
            $completedTasks = (int) $metrics->sum('completed_tasks');

            $weeklyTrend->push((object) [
                'label' => $weekStart->format('d/m') . ' - ' . $weekEnd->format('d/m'),
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0,
            ]);
        }

        $rekapKandang = DB::table('jadwal_pakan')
            ->join('satwa', 'satwa.id', '=', 'jadwal_pakan.satwa_id')
            ->select(
                'satwa.kandang',
                DB::raw('COUNT(jadwal_pakan.id) as total_tugas'),
                DB::raw("SUM(CASE WHEN jadwal_pakan.status = 'Selesai' THEN 1 ELSE 0 END) as tugas_selesai")
            )
            ->groupBy('satwa.kandang')
            ->orderBy('satwa.kandang')
            ->get();

        return view('dwh.index', compact(
            'totalSatwa',
            'totalJadwal',
            'jadwalSelesai',
            'jadwalPending',
            'persentaseKepatuhan',
            'activePetugas',
            'dailyTrend',
            'weeklyTrend',
            'rekapKandang'
        ));
    }
}