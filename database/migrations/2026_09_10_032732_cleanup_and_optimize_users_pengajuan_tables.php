<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 1. Hancurkan jembatan (Foreign Key) terlebih dahulu
            $table->dropForeign('users_atasan_id_foreign');
            
            // 2. Baru hapus kolomnya
            $table->dropColumn(['sisa_cuti_tahunan', 'atasan_id']);
        });
        
        // 1. Drop kolom redundan di tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sisa_cuti_tahunan', 'atasan_id']);
        });

        // 2. Tambahkan Index untuk mempercepat query EIS Dashboard
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->index('status_pengajuan');
            $table->index('approval_step');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sisa_cuti_tahunan')->nullable();
            $table->foreignId('atasan_id')->nullable();
        });

        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->dropIndex(['status_pengajuan']);
            $table->dropIndex(['approval_step']);
        });
    }
};