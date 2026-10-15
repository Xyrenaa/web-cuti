<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->unique();
            $table->string('nilai')->nullable(); // NULL = tanpa batas
            $table->foreignId('diubah_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('riwayat_pengaturan_cuti', function (Blueprint $table) {
            $table->id();
            $table->string('kunci')->index();
            $table->string('nilai_lama')->nullable();
            $table->string('nilai_baru')->nullable();
            $table->string('dasar_peraturan');
            $table->timestamp('pernyataan_pada');
            $table->foreignId('dilakukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Nilai awal = aturan yang selama ini tertanam di kode (12 dan 6).
        DB::table('pengaturan_cuti')->insert([
            ['kunci' => 'jatah_cuti_dasar',        'nilai' => '12', 'created_at' => now(), 'updated_at' => now()],
            ['kunci' => 'batas_sisa_cuti_dibawa',  'nilai' => '6',  'created_at' => now(), 'updated_at' => now()],
        ]);

        // Salinan aturan yang dipakai saat Tutup Tahun, supaya tahun lalu tetap bisa dijelaskan.
        Schema::table('tutup_tahun_logs', function (Blueprint $table) {
            $table->json('aturan')->nullable()->after('jumlah_pegawai');
        });
    }

    public function down(): void
    {
        Schema::table('tutup_tahun_logs', function (Blueprint $table) {
            $table->dropColumn('aturan');
        });
        Schema::dropIfExists('riwayat_pengaturan_cuti');
        Schema::dropIfExists('pengaturan_cuti');
    }
};