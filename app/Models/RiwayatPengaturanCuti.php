<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatPengaturanCuti extends Model
{
    protected $table = 'riwayat_pengaturan_cuti';

    protected $fillable = [
        'kunci', 'nilai_lama', 'nilai_baru', 'diterapkan_ke_pegawai', 'jumlah_pegawai_terdampak',
        'dasar_peraturan', 'pernyataan_pada', 'dilakukan_oleh',
    ];

    protected function casts(): array
    {
        return ['pernyataan_pada' => 'datetime'];
    }

    public function pelaku()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}