<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WhatsAppLinkGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AdminPetugasController extends Controller
{
    public function index()
    {
        $petugas = User::query()
            ->where('role', 'petugas')
            ->orderBy('is_active')
            ->orderBy('approved_at')
            ->orderBy('name')
            ->paginate(15);
        $activeCount = User::where('role', 'petugas')->where('is_active', true)->count();
        $pendingCount = User::where('role', 'petugas')->whereNull('approved_at')->count();
        $inactiveCount = User::where('role', 'petugas')
            ->where('is_active', false)
            ->whereNotNull('approved_at')
            ->count();

        return view('admin.petugas.index', compact('petugas', 'activeCount', 'pendingCount', 'inactiveCount'));
    }

    public function updateStatus(Request $request, User $user)
    {
        abort_unless($user->role === 'petugas', 404);

        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $isAwaitingApproval = ! $user->approved_at;
        $user->is_active = $validated['is_active'];
        if ($user->is_active && ! $user->approved_at) {
            $user->approved_at = now();
        }
        $user->save();

        return redirect()
            ->route('admin.petugas.index')
            ->with('success', ! $user->is_active
                ? 'Akun petugas berhasil dinonaktifkan.'
                : ($isAwaitingApproval
                    ? 'Akun petugas berhasil disetujui dan diaktifkan.'
                    : 'Akun petugas berhasil diaktifkan kembali.'));
    }

    public function updatePhone(Request $request, User $user, WhatsAppLinkGenerator $whatsAppLinkGenerator)
    {
        abort_unless($user->role === 'petugas', 404);

        if (! Schema::hasColumn('users', 'nomor_hp')) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors([
                    'nomor_hp' => 'Kolom nomor HP belum tersedia. Jalankan php artisan migrate lalu coba lagi.',
                ]);
        }

        $validated = $request->validate([
            'nomor_hp' => ['required', 'string', 'max:32', 'regex:/^\+?[0-9\s().-]+$/'],
        ]);
        $nomorHp = $whatsAppLinkGenerator->normalizePhone($validated['nomor_hp']);

        if ($nomorHp === null) {
            throw ValidationException::withMessages([
                'nomor_hp' => 'Nomor HP harus memiliki 8 sampai 15 digit setelah normalisasi.',
            ]);
        }

        $user->update(['nomor_hp' => $nomorHp]);

        return redirect()
            ->route('admin.petugas.index')
            ->with('success', 'Nomor WhatsApp Petugas berhasil disimpan.');
    }
}
