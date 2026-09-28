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
        Schema::create('tutup_tahun_logs', function (Blueprint $table) {
    $table->id();
    $table->unsignedSmallInteger('tahun_ditutup');
    $table->unsignedInteger('jumlah_pegawai');
    $table->foreignId('dilakukan_oleh')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutup_tahun_logs');
    }
};
