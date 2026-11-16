<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satwa extends Model
{
    protected $table = 'satwa';

    protected $fillable = [
        'nama_satwa',
        'kategori',
        'jumlah_ekor',
        'kandang',
        'status',
        'catatan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}