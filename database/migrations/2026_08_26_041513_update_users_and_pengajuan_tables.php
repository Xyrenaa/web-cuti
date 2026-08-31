<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah Relasi Atasan & Info Karyawan di Tabel Users (Dengan Pengecekan)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'atasan_id')) {
                $table->foreignId('atasan_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->nullable()->after('atasan_id');
            }
            if (!Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'divisi')) {
                $table->string('divisi')->nullable()->after('jabatan');
            }
        });

        // 2. Rombak Kolom Tabel Pengajuan Cuti (Dengan Pengecekan)
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_cutis', 'file_lampiran')) {
                $table->dropColumn('file_lampiran'); 
            }
            if (!Schema::hasColumn('pengajuan_cutis', 'lokasi')) {
                $table->string('lokasi')->after('alasan')->nullable();
            }
            if (!Schema::hasColumn('pengajuan_cutis', 'surat_pengajuan')) {
                $table->string('surat_pengajuan')->after('lokasi')->nullable(); 
            }
            if (!Schema::hasColumn('pengajuan_cutis', 'bukti_pendukung')) {
                $table->json('bukti_pendukung')->nullable()->after('surat_pengajuan'); 
            }
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
            $table->dropColumn(['atasan_id', 'nip', 'jabatan', 'divisi']);
        });
    }
};