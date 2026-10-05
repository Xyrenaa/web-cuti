<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Penanda "wajib konfirmasi email & ganti password" saat login pertama.
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'wajib_ganti_kredensial')) {
                $table->boolean('wajib_ganti_kredensial')->default(false)->after('password');
            }
        });

        // 2. Login sekarang pakai NIP polos 18 digit. Rapikan NIP lama yang
        //    mungkin tersimpan dengan spasi/titik (sisa import lama). Kalau hasil
        //    rapi bentrok dengan NIP user lain, dilewati agar tidak melanggar unique.
        DB::table('users')->whereNotNull('nip')->orderBy('id')->each(function ($u) {
            $bersih = preg_replace('/\D/', '', (string) $u->nip);

            if ($bersih === '' || $bersih === $u->nip) {
                return;
            }

            $bentrok = DB::table('users')->where('nip', $bersih)->where('id', '!=', $u->id)->exists();

            if (! $bentrok) {
                DB::table('users')->where('id', $u->id)->update(['nip' => $bersih]);
            }
        });

        // 3. Akun yang masih memakai email sementara/password awal (hasil import
        //    & seeder Kepala, domain @otban3.com) wajib melewati pop-up.
        //    Akun yang daftar sendiri (email + password pilihan sendiri) tidak disentuh.
        DB::table('users')->where('email', 'like', '%@otban3.com')->update(['wajib_ganti_kredensial' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wajib_ganti_kredensial');
        });
    }
};