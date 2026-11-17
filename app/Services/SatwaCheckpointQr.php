<?php

namespace App\Services;

use App\Models\Satwa;

class SatwaCheckpointQr
{
    public function payloadFor(Satwa $satwa): string
    {
        return json_encode([
            'version' => 1,
            'satwa_id' => $satwa->id,
            'signature' => $this->signatureFor($satwa->id),
        ], JSON_THROW_ON_ERROR);
    }

    public function satwaIdFromPayload(string $payload): ?int
    {
        $data = json_decode($payload, true);

        if (! is_array($data) || ($data['version'] ?? null) !== 1) {
            return null;
        }

        $satwaId = filter_var($data['satwa_id'] ?? null, FILTER_VALIDATE_INT);
        $signature = $data['signature'] ?? null;

        if (
            $satwaId === false
            || $satwaId === null
            || $satwaId < 1
            || ! is_string($signature)
        ) {
            return null;
        }

        return hash_equals($this->signatureFor($satwaId), $signature) ? $satwaId : null;
    }

    private function signatureFor(int $satwaId): string
    {
        return hash_hmac('sha256', "satwa:{$satwaId}:v1", (string) config('app.key'));
    }
}
