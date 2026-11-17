<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'nomor_hp')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('nomor_hp', 32)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        // Keep the repaired column; the original add-column migration owns its rollback.
    }
};
