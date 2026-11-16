<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jadwal_pakan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satwa_id')->constrained('satwa')->onDelete('cascade');
            $table->string('petugas'); // Nama petugas hasil pengacakan
            $table->date('tanggal_tugas');
            $table->enum('status', ['Pending', 'Selesai'])->default('Pending');
            $table->text('catatan_petugas')->nullable();
            $table->string('bukti_foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pakan');
    }
};