<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPakan extends Model
{
    protected $table = 'jadwal_pakan';

    protected $fillable = [
        'satwa_id',
        'user_id',
        'petugas',
        'tanggal_tugas',
        'status',
        'catatan_petugas',
        'bukti_foto',
    ];

    public function satwa(): BelongsTo
    {
        return $this->belongsTo(Satwa::class, 'satwa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}