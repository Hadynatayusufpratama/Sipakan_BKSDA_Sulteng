<?php

namespace App\Services;

use App\Models\JadwalPakan;

class WhatsAppLinkGenerator
{
    public function normalizePhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return strlen($digits) >= 8 && strlen($digits) <= 15
            ? $digits
            : null;
    }

    public function forTask(JadwalPakan $jadwal): ?string
    {
        $user = $jadwal->user;
        $satwa = $jadwal->satwa;

        if (! $user || ! $satwa || ! $user->nomor_hp) {
            return null;
        }

        $phone = $this->normalizePhone($user->nomor_hp);

        if ($phone === null) {
            return null;
        }

        $message = sprintf(
            'Halo %s, Anda mendapat tugas memberi makan satwa %s di %s pada tanggal %s. Mohon segera lakukan scan QR kandang. Terima kasih.',
            $user->name,
            $satwa->nama_satwa,
            $satwa->kandang,
            $jadwal->tanggal_tugas
        );

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }
}
