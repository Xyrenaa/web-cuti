<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Relasi Atasan di Tabel Users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('atasan_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        // 2. Rombak Kolom Tabel Pengajuan Cuti
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->dropColumn('file_lampiran'); // Hapus yang lama
            
            $table->string('lokasi')->after('alasan');
            $table->string('surat_pengajuan')->after('lokasi'); // Wajib
            $table->string('bukti_pendukung')->nullable()->after('surat_pengajuan'); // Opsional
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->string('file_lampiran')->nullable();
            $table->dropColumn(['lokasi', 'surat_pengajuan', 'bukti_pendukung']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['atasan_id']);
            $table->dropColumn('atasan_id');
        });
    }
};