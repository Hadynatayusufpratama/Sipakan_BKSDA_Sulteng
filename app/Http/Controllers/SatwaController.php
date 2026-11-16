<?php

namespace App\Http\Controllers;

use App\Models\Satwa;
use App\Models\JadwalPakan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SatwaController extends Controller
{
    public function index()
    {
        $satwa = Satwa::all();
        return view('satwa.index', compact('satwa'));
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
    public function jadwalIndex()
    {
        $jadwal = JadwalPakan::with(['satwa', 'user'])->latest()->get();
        return view('jadwal.index', compact('jadwal'));
    }

    public function acakJadwal()
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
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'name']);

            if ($semuaSatwa->isEmpty() || $petugasList->isEmpty()) {
                return [
                    'error' => $semuaSatwa->isEmpty()
                        ? 'Belum ada satwa aktif untuk diacak jadwalnya.'
                        : 'Belum ada akun Petugas aktif untuk menerima jadwal.',
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

        return redirect()->back()->with('success', 'Berhasil mengacak dan mendistribusikan jadwal pakan petugas!');
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
            ->first();

        $totalJadwal = (int) $jadwalMetrics->total_jadwal;
        $jadwalSelesai = (int) $jadwalMetrics->jadwal_selesai;
        $persentaseKepatuhan = $totalJadwal > 0 ? round(($jadwalSelesai / $totalJadwal) * 100) : 0;

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

        return view('dwh.index', compact('totalSatwa', 'totalJadwal', 'jadwalSelesai', 'persentaseKepatuhan', 'rekapKandang'));
    }
}