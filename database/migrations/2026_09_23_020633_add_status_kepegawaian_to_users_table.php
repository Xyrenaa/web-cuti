<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nullable, tanpa default — supaya pegawai yang sudah ter-import
            // sebelum kolom ini ada tidak otomatis ke-tandai "PNS" secara keliru.
            $table->enum('status_kepegawaian', ['PNS', 'PPPK'])->nullable()->after('level_jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status_kepegawaian');
        });
    }
};