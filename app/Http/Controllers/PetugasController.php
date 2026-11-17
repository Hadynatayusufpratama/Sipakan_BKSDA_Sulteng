<?php

namespace App\Http\Controllers;

use App\Models\JadwalPakan;
use App\Models\Satwa;
use App\Services\SatwaCheckpointQr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class PetugasController extends Controller
{
    private const CHECKPOINT_SESSION_KEY = 'petugas_checkpoint_scans';

    public function dashboard(Request $request)
    {
        $today = now()->toDateString();
        $jadwalTugas = JadwalPakan::with(['satwa', 'user'])
            ->where('user_id', $request->user()->id)
            ->where(function ($query) use ($today) {
                $query->whereDate('tanggal_tugas', $today)
                    ->orWhere(function ($query) use ($today) {
                        $query->where('status', 'Pending')
                            ->whereDate('tanggal_tugas', '<=', $today);
                    });
            })
            ->orderBy('status')
            ->orderByDesc('tanggal_tugas')
            ->orderBy('id')
            ->get();

        $jadwalHariIni = $jadwalTugas->where('tanggal_tugas', $today);
        $totalTugas = $jadwalHariIni->count();
        $tugasSelesai = $jadwalHariIni->where('status', 'Selesai')->count();
        $pendingTasksToday = JadwalPakan::with('satwa')
            ->where('user_id', $request->user()->id)
            ->whereDate('tanggal_tugas', $today)
            ->where('status', 'Pending')
            ->orderBy('id')
            ->get();
        $totalSatwa = Satwa::where('is_active', true)->count();
        $checkpointSessionKey = self::CHECKPOINT_SESSION_KEY . '_' . $request->user()->id;
        $pendingTaskIds = $jadwalTugas
            ->where('status', 'Pending')
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
        $checkpointScans = array_intersect_key(
            session($checkpointSessionKey, []),
            array_fill_keys($pendingTaskIds, true)
        );

        foreach ($checkpointScans as $taskId => $scannedAt) {
            $checkpointScans[$taskId] = Carbon::parse($scannedAt);
        }

        return view('petugas.dashboard', compact(
            'jadwalTugas',
            'totalTugas',
            'tugasSelesai',
            'totalSatwa',
            'checkpointScans',
            'pendingTasksToday'
        ));
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

    public function checkpoint(Request $request, SatwaCheckpointQr $checkpointQr): JsonResponse
    {
        $validated = $request->validate([
            'task_id' => 'required|integer|min:1',
            'qr_payload' => 'required|string|max:512',
        ]);
        $satwaId = $checkpointQr->satwaIdFromPayload($validated['qr_payload']);

        if ($satwaId === null) {
            return response()->json(['message' => 'QR kandang tidak valid.'], 422);
        }

        $today = now()->toDateString();

        $checkpointSessionKey = self::CHECKPOINT_SESSION_KEY . '_' . $request->user()->id;
        $sessionScans = session($checkpointSessionKey, []);
        $checkpointResult = DB::transaction(function () use ($validated, $satwaId, $today, $request, $sessionScans) {
            $assignmentQuery = JadwalPakan::query()
                ->where('satwa_id', $satwaId)
                ->where('user_id', $request->user()->id)
                ->where('status', 'Pending');

            $jadwal = (clone $assignmentQuery)
                ->whereKey($validated['task_id'])
                ->where('tanggal_tugas', $today)
                ->lockForUpdate()
                ->first();

            if (! $jadwal) {
                $jadwal = (clone $assignmentQuery)
                    ->where('tanggal_tugas', $today)
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();
            }

            if (! $jadwal) {
                $jadwal = (clone $assignmentQuery)
                    ->orderByDesc('tanggal_tugas')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();
            }

            if (! $jadwal) {
                return null;
            }

            $scannedAt = isset($sessionScans[$jadwal->id])
                ? Carbon::parse($sessionScans[$jadwal->id])
                : now();

            if (! isset($sessionScans[$jadwal->id])) {
                $jadwal->touch();
            }

            return [$jadwal->fresh('satwa'), $scannedAt];
        });

        if (! $checkpointResult) {
            $message = 'Jadwal pakan untuk kandang ini belum didistribusikan hari ini.';

            return response()->json([
                'message' => $message,
                'errors' => ['task_id' => [$message]],
            ], 422);
        }

        [$jadwal, $scannedAt] = $checkpointResult;
        $sessionScans[$jadwal->id] = $scannedAt->toIso8601String();
        session()->put($checkpointSessionKey, $sessionScans);

        return response()->json([
            'task_id' => $jadwal->id,
            'satwa_name' => $jadwal->satwa?->nama_satwa ?? 'Satwa',
            'scanned_at' => $scannedAt->toIso8601String(),
            'scanned_at_display' => $scannedAt->format('d/m/Y H:i:s'),
        ]);
    }

    public function updateLaporan(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_petugas' => 'required|string|max:2000',
            'bukti_foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $userId = $request->user()->id;
        $checkpointSessionKey = self::CHECKPOINT_SESSION_KEY . '_' . $userId;
        $sessionScans = session($checkpointSessionKey, []);

        if (! isset($sessionScans[$id])) {
            abort(404);
        }

        $photoPath = $request->file('bukti_foto')->store('bukti_pakan', 'public');

        if ($photoPath === false) {
            throw new RuntimeException('Bukti foto gagal disimpan.');
        }

        try {
            $oldPhotoPath = DB::transaction(function () use ($id, $validated, $photoPath, $userId) {
                $jadwal = JadwalPakan::query()
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

        unset($sessionScans[$id]);

        if ($sessionScans === []) {
            session()->forget($checkpointSessionKey);
        } else {
            session()->put($checkpointSessionKey, $sessionScans);
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