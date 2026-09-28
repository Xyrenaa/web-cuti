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
        Schema::table('jenis_cutis', function (Blueprint $table) {
            //
            $table->boolean('khusus_perempuan')->default(false)->after('untuk_pppk');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jenis_cutis', function (Blueprint $table) {
            //
            $table->dropColumn('khusus_perempuan');
        });
        
    }
};
