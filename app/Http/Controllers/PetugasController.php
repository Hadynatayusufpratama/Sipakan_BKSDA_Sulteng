<?php

namespace App\Http\Controllers;

use App\Models\JadwalPakan;
use App\Models\Satwa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PetugasController extends Controller
{
    public function dashboard(Request $request)
    {
        $jadwalTugas = JadwalPakan::with(['satwa', 'user'])
            ->whereDate('tanggal_tugas', now()->toDateString())
            ->where('user_id', $request->user()->id)
            ->orderBy('status')
            ->orderBy('id')
            ->get();

        $totalTugas = $jadwalTugas->count();
        $tugasSelesai = $jadwalTugas->where('status', 'Selesai')->count();
        $totalSatwa = Satwa::where('is_active', true)->count();

        return view('petugas.dashboard', compact('jadwalTugas', 'totalTugas', 'tugasSelesai', 'totalSatwa'));
    }

    public function satwaIndex()
    {
        $satwa = Satwa::query()
            ->where('is_active', true)
            ->orderBy('nama_satwa')
            ->get();

        return view('petugas.satwa', compact('satwa'));
    }

    public function riwayat(Request $request)
    {
        $riwayatTugas = JadwalPakan::with('satwa')
            ->where('status', 'Selesai')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('petugas.riwayat', compact('riwayatTugas'));
    }

    public function profil()
    {
        return view('petugas.profil');
    }

    public function updateLaporan(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_petugas' => 'required|string|max:2000',
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $photoPath = $request->file('bukti_foto')->store('bukti_pakan', 'public');

        if ($photoPath === false) {
            throw new RuntimeException('Bukti foto gagal disimpan.');
        }

        $userId = $request->user()->id;

        try {
            $oldPhotoPath = DB::transaction(function () use ($id, $validated, $photoPath, $userId) {
                $jadwal = JadwalPakan::query()
                    ->whereDate('tanggal_tugas', now()->toDateString())
                    ->where('user_id', $userId)
                    ->where('status', 'Pending')
                    ->lockForUpdate()
                    ->findOrFail($id);

                $oldPhotoPath = $jadwal->bukti_foto;

                $jadwal->update([
                    'status' => 'Selesai',
                    'catatan_petugas' => $validated['catatan_petugas'],
                    'bukti_foto' => $photoPath,
                ]);

                return $oldPhotoPath;
            });
        } catch (Throwable $exception) {
            if (! Storage::disk('public')->delete($photoPath)) {
                Log::error('Bukti foto gagal dibersihkan setelah laporan gagal disimpan.', [
                    'path' => $photoPath,
                    'jadwal_id' => $id,
                ]);
            }

            throw $exception;
        }

        if ($oldPhotoPath && $oldPhotoPath !== $photoPath && ! Storage::disk('public')->delete($oldPhotoPath)) {
            Log::warning('Bukti foto laporan sebelumnya tidak dapat dihapus.', [
                'path' => $oldPhotoPath,
                'jadwal_id' => $id,
            ]);
        }

        return redirect()
            ->route('petugas.dashboard')
            ->with('success', 'Laporan pakan berhasil dikirim ke Admin!');
    }
}