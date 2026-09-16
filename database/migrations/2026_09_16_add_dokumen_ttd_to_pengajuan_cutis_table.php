<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            // Riwayat dokumen yang sudah ditandatangani tiap tahap approval,
            // sebagai JSON array: [{step, peran, nama, file, waktu}, ...]
            $table->text('dokumen_ttd')->nullable()->after('bukti_pendukung');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            $table->dropColumn('dokumen_ttd');
        });
    }
};