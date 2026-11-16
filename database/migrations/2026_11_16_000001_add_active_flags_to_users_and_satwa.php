<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('approved_at')->nullable();
        });

        DB::table('users')
            ->where('is_active', true)
            ->update(['approved_at' => now()]);

        Schema::table('satwa', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->index();
        });
    }

    public function down(): void
    {
        Schema::table('satwa', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->dropIndex(['is_active']);
            $table->dropColumn('is_active');
        });
    }
};
