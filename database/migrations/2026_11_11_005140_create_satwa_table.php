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
        Schema::create('satwa', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satwa');
            $table->string('kategori');
            $table->integer('jumlah_ekor');
            $table->string('kandang');
            $table->string('status')->default('Dalam pemantauan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satwa');
    }
};