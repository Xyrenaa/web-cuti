<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_pengaturan_cuti', function (Blueprint $table) {
            $table->boolean('diterapkan_ke_pegawai')->default(false)->after('nilai_baru');
            $table->unsignedInteger('jumlah_pegawai_terdampak')->nullable()->after('diterapkan_ke_pegawai');
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_pengaturan_cuti', function (Blueprint $table) {
            $table->dropColumn(['diterapkan_ke_pegawai', 'jumlah_pegawai_terdampak']);
        });
    }
};