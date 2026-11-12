<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_cutis', 'surat_pengajuan_nama')) {
                $table->string('surat_pengajuan_nama')->nullable()->after('surat_pengajuan');
            }
            if (! Schema::hasColumn('pengajuan_cutis', 'surat_cuti_final')) {
                $table->string('surat_cuti_final')->nullable()->after('dokumen_ttd');
            }
            if (! Schema::hasColumn('pengajuan_cutis', 'surat_cuti_final_nama')) {
                $table->string('surat_cuti_final_nama')->nullable()->after('surat_cuti_final');
            }
            if (! Schema::hasColumn('pengajuan_cutis', 'surat_cuti_final_at')) {
                $table->timestamp('surat_cuti_final_at')->nullable()->after('surat_cuti_final_nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            foreach (['surat_pengajuan_nama', 'surat_cuti_final', 'surat_cuti_final_nama', 'surat_cuti_final_at'] as $kolom) {
                if (Schema::hasColumn('pengajuan_cutis', $kolom)) {
                    $table->dropColumn($kolom);
                }
            }
        });
    }
};