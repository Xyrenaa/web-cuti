<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            if (! Schema::hasColumn('pengajuan_cutis', 'plh_user_id')) {
                $table->foreignId('plh_user_id')
                    ->nullable()
                    ->after('approval_step')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_cutis', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_cutis', 'plh_user_id')) {
                $table->dropConstrainedForeignId('plh_user_id');
            }
        });
    }
};