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
        // 1. Membuat tabel untuk level tertinggi (Bagian TU / Bidang)
        Schema::create('bagian_bidangs', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); 
            // Penanda khusus untuk membedakan jalur approval Tata Usaha vs Bidang
            $table->boolean('is_tu')->default(false); 
            $table->timestamps();
        });

        // 2. Membuat tabel untuk level di bawahnya (Sub-Bagian TU / Seksi)
        Schema::create('sub_bagian_seksis', function (Blueprint $table) {
            $table->id();
            // Relasi ke tabel bagian_bidangs
            $table->foreignId('bagian_bidang_id')->constrained('bagian_bidangs')->cascadeOnDelete();
            $table->string('nama');
            $table->timestamps();
        });

        // 3. Mengubah tabel users yang sudah ada
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom lama (Sesuaikan nama kolom dengan yang ada di database-mu)
            $table->dropColumn(['divisi', 'jabatan']); 
            
            // Tambahkan foreign key yang mengarah ke struktur baru
            $table->foreignId('bagian_bidang_id')->nullable()->constrained('bagian_bidangs');
            $table->foreignId('sub_bagian_seksi_id')->nullable()->constrained('sub_bagian_seksis');
            
            // Gunakan ENUM untuk level jabatan agar rapi
            $table->enum('level_jabatan', [
                'Kepala Kantor', 
                'Kepala Bagian/Bidang', 
                'Kepala Seksi/Sub-Bagian', 
                'Pegawai'
            ])->default('Pegawai');
            
            // Tambahkan jatah cuti
            $table->integer('jatah_cuti')->default(12);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Kembalikan tabel users seperti semula jika perintah php artisan migrate:rollback dijalankan
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['bagian_bidang_id']);
            $table->dropForeign(['sub_bagian_seksi_id']);
            $table->dropColumn(['bagian_bidang_id', 'sub_bagian_seksi_id', 'level_jabatan', 'jatah_cuti']);
            
            // Buat ulang kolom lama (Opsional, tergantung kebutuhan rollback-mu)
            $table->string('divisi')->nullable();
            $table->string('jabatan')->nullable();
        });

        // 2. Hapus tabel organisasi (Mulai dari child ke parent)
        Schema::dropIfExists('sub_bagian_seksis');
        Schema::dropIfExists('bagian_bidangs');
    }
};