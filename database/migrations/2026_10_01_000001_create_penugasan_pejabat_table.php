<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penugasan Plh (Pelaksana Harian) & Plt (Pelaksana Tugas).
     *
     * Jabatan yang digantikan diidentifikasi lewat (jabatan_role, bagian_bidang_id,
     * sub_bagian_seksi_id) — bukan hanya lewat orangnya — karena pada Plt jabatannya
     * kosong sehingga tidak ada pejabat definitif yang bisa dirujuk.
     */
    public function up(): void
    {
        Schema::create('penugasan_pejabat', function (Blueprint $table) {
            $table->id();

            $table->string('jenis', 3);                 // PLH | PLT
            $table->string('jabatan_role');             // nama role Spatie yang digantikan, mis. "Kepala Seksi"
            $table->foreignId('bagian_bidang_id')->nullable()->constrained('bagian_bidangs')->nullOnDelete();
            $table->foreignId('sub_bagian_seksi_id')->nullable()->constrained('sub_bagian_seksis')->nullOnDelete();

            // Plh: kepala yang sedang berhalangan. Plt: boleh kosong (jabatan lowong).
            $table->foreignId('pejabat_definitif_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('pengganti_id')->constrained('users')->cascadeOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable(); // Plt: kosong = sampai dicabut / ada pejabat definitif

            $table->string('status', 15)->default('aktif'); // aktif | selesai | dicabut | dialihkan
            $table->string('nomor_surat')->nullable();
            $table->text('alasan')->nullable();
            $table->string('keterangan_akhir')->nullable(); // alasan dicabut / dialihkan
            $table->timestamp('berakhir_pada')->nullable();

            // Jejak tukar posisi: penugasan baru menunjuk ke penugasan lama yang digantikannya.
            $table->foreignId('dialihkan_dari_id')->nullable()->constrained('penugasan_pejabat')->nullOnDelete();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['jabatan_role', 'status']);
            $table->index(['pengganti_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penugasan_pejabat');
    }
};