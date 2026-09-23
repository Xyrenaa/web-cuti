<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_cutis', function (Blueprint $table) {
            // Default true supaya jenis cuti lama yang belum diisi eksplisit
            // (sebelum kolom ini ada) tetap tampil untuk semua, aman.
            $table->boolean('untuk_pppk')->default(true)->after('wajib_lampiran');
        });
    }

    public function down(): void
    {
        Schema::table('jenis_cutis', function (Blueprint $table) {
            $table->dropColumn('untuk_pppk');
        });
    }
};