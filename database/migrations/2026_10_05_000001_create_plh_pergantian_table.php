<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Riwayat penukaran PLH oleh Superadmin (siapa diganti siapa, kenapa, kapan). */
    public function up(): void
    {
        Schema::create('plh_pergantian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_cuti_id')->constrained('pengajuan_cutis')->cascadeOnDelete();
            $table->foreignId('plh_lama_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('plh_baru_id')->constrained('users')->cascadeOnDelete();
            $table->text('alasan');
            $table->foreignId('dilakukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plh_pergantian');
    }
};